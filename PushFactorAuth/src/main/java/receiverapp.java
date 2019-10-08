import java.io.*;
import java.net.ServerSocket;
import java.net.Socket;
import java.nio.charset.StandardCharsets;
import java.util.Scanner;
import java.util.concurrent.Executors;
import java.util.concurrent.ExecutorService;
import java.net.*;

//import java.awt.*; //import these for GUI
//import javax.swing.*;
public class receiverapp
{
  public static void main(String[] args) throws IOException {
    System.out.println("Receiver App!");
    String hostName = "PushAuthApp";
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

  private static class PushResponder implements Runnable
  {
    private Socket socket;
    PushResponder(Socket socket)
    {
      this.socket = socket;
    }

    @Override
    public void run()
    {
      int codeLength = 4; //length of the random auth code before each response option
      String cancelCode = ""; //needed to store "Cancel" code after socket is closed (to authorize deletion of session file)
      String session_id = ""; //needed to identify which session file should be deleted from server
      Boolean showCancel = false; //Cancel option only appears when a session is authenticated
      //System.out.println("Login request from a real site!");
      System.out.println("Connected " + socket);
      try
      {
        Scanner in = new Scanner(socket.getInputStream());
        PrintWriter out = new PrintWriter(socket.getOutputStream(), true);
        //todo: needs to wait for a confirmation from server that code was received, then close socket?
        if(in.hasNextLine())
        {
          String inputString = in.nextLine();
          //String inputString = "1234Approve-5678Approve 10 min-4321Deny-8756Cancel Login-sessionID"; //example test string
          System.out.println("Auth codes received: " + inputString);
          //parse string, then separate into auth strings and option text strings
          String[] codes = inputString.split("-");
          /*
          todo: insert check that codes[] is large enough
           - no fewer than 4 codes (approve,deny,cancel,session_id)
           - first code must be Approve, last 3 must be Deny, Cancel Login, session_id
          */
          System.out.println("Select One:");
          for (int i = 0; i < codes.length-2; i++) { //last 2 codes are Cancel and session_id
            String codeOut = codes[i].substring(codeLength); //cuts out response code
            System.out.println((i + 1) + ". " + codeOut);
          }
          //request input for which option to select
          Scanner responseReq = new Scanner(System.in);
          int response = responseReq.nextInt();
          //based on user input, output response code back to login site
          String responseCode = codes[response - 1].substring(0, codeLength);
          System.out.println(responseCode);
          out.println(responseCode);

          //if response was not Deny, allow for Cancel option to appear
          if(response - 1 != codes.length - 3) { //&& response-1 != 1) {
            //System.out.println(response);
            cancelCode = codes[codes.length-2].substring(0,codeLength);
            session_id = codes[codes.length-1];
            showCancel = true;
          }
        }
      } catch (Exception e)
      {
        System.out.println("Error:" + socket);
      } finally
      {
        try { socket.close(); } catch (IOException e) {}
        System.out.println("Closed: " + socket);
      }

      //make http request to localhost:8888/logout.php?action=session_id if response != deny
      if(showCancel)
      {
        //request input to activate "Cancel Login"
        System.out.println("Cancel Login: y or n?");
        Scanner responseReq = new Scanner(System.in);
        char response = responseReq.next().charAt(0);
        if(response == 'y') {
          //System.out.println("response = " + response);
          String url = "http://localhost:8888/logout.php";
          String urlParameters = "action=" + session_id;
          try {
            httpPost(url, urlParameters);
          } catch (IOException e) {
            e.printStackTrace();
          }
        }
        System.out.println("Waiting for new login request.");
      }
    }

    private void httpPost(String url, String urlParameters) throws IOException
    {
      HttpURLConnection con = null;
      byte[] postData = urlParameters.getBytes(StandardCharsets.UTF_8);

      try {
        URL myurl = new URL(url);
        con = (HttpURLConnection) myurl.openConnection();

        con.setDoOutput(true);
        con.setRequestMethod("POST");
        con.setRequestProperty("User-Agent", "Java client");
        con.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");

        try (DataOutputStream wr = new DataOutputStream(con.getOutputStream())) {
          wr.write(postData);
        }
      } finally {
        if(con!=null) {
          con.disconnect();
        }
      }
    }
  }
}
