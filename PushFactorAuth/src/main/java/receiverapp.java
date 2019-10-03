import java.io.IOException;
import java.io.InputStream;
import java.io.PrintWriter;
import java.net.ServerSocket;
import java.net.Socket;
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
      //String cancelCode = ""; //needed to store "Cancel" code after socket is closed (to authorize deletion of session file)
      String session_id = ""; //needed to identify which session file should be deleted from server
      Boolean showCancel = false; //Cancel option only appears when a session is authenticated
      //System.out.println("Login request from a real site!");
      System.out.println("Connected " + socket);
      try
      {
        Scanner in = new Scanner(socket.getInputStream());
        PrintWriter out = new PrintWriter(socket.getOutputStream(), true);
        //needs to wait for a confirmation from server that code was received, then close socket
        while (in.hasNextLine())//should only ever BE one line, change to complain if more than 1
        {
          String inputString = in.nextLine();
          //String inputString = "1234approve-5678approve 10 min-4321deny";
          System.out.println("Auth codes received: " + inputString);
          //parse string, separate into auth strings and option text strings
          String[] codes = inputString.split("-");
          //insert check that codes[] is large enough
          // - no fewer than 4 codes (approve,deny,cancel,session_id)
          System.out.println("Select One:");
          for (int i = 0; i < codes.length-2; i++) { //last 2 codes are Cancel and session_id
            String codeOut = codes[i].substring(4); //cuts out response code
            System.out.println((i + 1) + ". " + codeOut);
          }
          //request input for which option to select
          Scanner responseReq = new Scanner(System.in);
          int response = responseReq.nextInt();
          //based on input, output response code
          String responseCode = codes[response - 1].substring(0, 4);
          System.out.println(responseCode);
          out.println(responseCode);

          //if response was not Deny, allow for Cancel option to appear
          if(response - 1 != codes.length - 3 && response-1 != 1) {
            //System.out.println(response);
            //cancelCode = codes[codes.length-2].substring(0,4);
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
        System.out.println("1. Cancel Login");
        Scanner responseReq = new Scanner(System.in);
        int response = responseReq.nextInt();
        String r = Integer.toString(response);
        if(r.equals("1")) {
          //System.out.println("response = 1");
          try {
            String url = "http://localhost:8888/logout.php?action=" + session_id;
            //System.out.println(url);
            URL urlObj = new URL(url);
            URLConnection urlCon = urlObj.openConnection();
            InputStream inputStream = urlCon.getInputStream();
            System.out.println("Session terminated.");
          } catch (MalformedURLException e) {
            System.out.println("The specified URL is malformed: " + e.getMessage());
          } catch (IOException e) {
            System.out.println("An I/O error occurs: " + e.getMessage());
          }
        }
      }
    }
  }
}
