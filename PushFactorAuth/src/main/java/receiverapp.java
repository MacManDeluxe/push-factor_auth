import java.io.*;
import java.net.ServerSocket;
import java.net.Socket;
import java.nio.charset.StandardCharsets;
import java.util.Scanner;
import java.util.concurrent.Executors;
import java.util.concurrent.ExecutorService;
import java.net.*;

//TODO: refactor name to ReceiverApp

public class receiverapp {
  public static void main(String[] args) throws IOException {
    System.out.println("Receiver App!");
    int portNumber = 8080;
    int nThreads = 20;  //max number of concurrent auth requests
    //create ServerSocket
    try (ServerSocket listener = new ServerSocket(portNumber)) {
      System.out.println("The receiver server is running...");
      ExecutorService pool = Executors.newFixedThreadPool(nThreads);
      while (true) {
        pool.execute(new PushResponder(listener.accept()));
      }
    }
  }

  private static class PushResponder implements Runnable {
    private Socket socket;
    PushResponder(Socket socket) {
      this.socket = socket;
    }

    @Override
    public void run() {
      int codeLength = 4; //length of the random auth code before each response option
      String cancelCode = ""; //needed to store "Cancel" code after socket is closed (to authorize deletion of session file)
      String session_id = ""; //needed to identify which session file should be deleted from server
      boolean showCancel = false; //Cancel option only appears when a session is authenticated
      //System.out.println("Login request from a real site!");
      System.out.println("Connected " + socket);
      try {
        Scanner in = new Scanner(socket.getInputStream());
        PrintWriter out = new PrintWriter(socket.getOutputStream(), true);
        if(in.hasNextLine()) {
          String inputString = in.nextLine();
          System.out.println("Auth codes received: " + inputString);
          //parse string, then separate into auth strings and option text strings
          String[] codes = inputString.split("-");
          /*
          todo: insert check that codes[] is large enough
           - no fewer than 4 codes (approve,deny,cancel,session_id)
           - first code must be Approve, last 3 must be Deny, Cancel Login, session_id
          */
          System.out.println("###################################");
          System.out.println("Select One:");
          for (int i = 0; i < codes.length-2; i++) { //last 2 codes are Cancel and session_id
            String codeOut = codes[i].substring(codeLength); //cuts out response code

            System.out.println((i + 1) + ". " + codeOut);
          }
          System.out.println("###################################");
          //request input for which option to select
          Scanner responseReq = new Scanner(System.in);
          int response = responseReq.nextInt();
          //based on user input, output response code back to login site
          String responseCode = codes[response - 1].substring(0, codeLength);
          System.out.println(responseCode);
          out.println(responseCode);

          //if response was not Deny, allow for Cancel option to appear (for demo, timed approve suppresses cancel option)
          if(response - 1 != codes.length - 3 && response-1 != 1) {
            cancelCode = codes[codes.length-2].substring(0,codeLength);
            session_id = codes[codes.length-1];
            showCancel = true;
          }
        }
      } catch (Exception e) {
        System.out.println("Error:" + socket);
      } finally {
        try { socket.close(); } catch (IOException e) { e.printStackTrace(); }
        System.out.println("Closed: " + socket);
      }

      //make http request to localhost:8888/logout.php?action=session_id if response != deny
      if(showCancel) {
        //request input to activate "Cancel Login"
        System.out.println("###################################");
        System.out.println("Cancel Login: y or n?");
        System.out.println("###################################");
        Scanner responseReq = new Scanner(System.in);
        char response = responseReq.next().charAt(0);
        if(response == 'y') {
          String url = "http://localhost:8888/logout.php";
          String urlParameters = "action=" + session_id + "&authCode=" + cancelCode;
          try {
            httpPost(url, urlParameters);
          } catch (IOException e) {
            e.printStackTrace();
          }
          System.out.println("Session Terminated");
        }
        System.out.println("Waiting for new login request...");
      }
    }

    private void httpPost(String url, String urlParameters) throws IOException {
      HttpURLConnection con = null;
      byte[] postData = urlParameters.getBytes(StandardCharsets.UTF_8);

      try {
        URL myUrl = new URL(url);

        con = (HttpURLConnection) myUrl.openConnection();

        con.setDoOutput(true);
        con.setRequestMethod("POST");
        con.setRequestProperty("User-Agent", "Java client");
        con.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");

        try (DataOutputStream wr = new DataOutputStream(con.getOutputStream())) {
          wr.write(postData);
          String postDataString = new String(postData);
          System.out.println(postDataString);
        }
        //read url response to ensure proper write
        StringBuilder content;
        try (BufferedReader in = new BufferedReader(new InputStreamReader(con.getInputStream()))) {
          String line;
          content = new StringBuilder();
          while ((line = in.readLine()) != null) {
            content.append(line);
            content.append(System.lineSeparator());
          }
        }
      } finally {
        if(con!=null) {
          con.disconnect();
        }
      }
    }
  }
}
