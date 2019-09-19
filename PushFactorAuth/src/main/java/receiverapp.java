import java.io.IOException;
import java.io.PrintWriter;
import java.net.ServerSocket;
import java.net.Socket;
import java.util.Scanner;
import java.util.concurrent.Executors;
import java.util.concurrent.ExecutorService;

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
      System.out.println("Login request from a real site!");
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
          System.out.println("Auth request received: " + inputString);
          //parse string, separate into auth strings and option text strings
          String[] codes = inputString.split("-");
          System.out.println("Select One:");
          for (int i = 0; i < codes.length; i++) {
            String codeOut = codes[i].substring(4); //cuts out response code
            System.out.println((i + 1) + ". " + codeOut);
          }
          //request input for which option to select
          Scanner responseReq = new Scanner(System.in);
          int response = responseReq.nextInt();
          //based on input, output response code
          System.out.println(codes[response - 1].substring(0, 4));
          out.println(codes[response - 1].substring(0, 4));
        }
      } catch (Exception e)
      {
        System.out.println("Error:" + socket);
      } finally
      {
        try { socket.close(); } catch (IOException e) {}
        System.out.println("Closed: " + socket);
      }
    }
  }
}
