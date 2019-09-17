//import java.awt.*;
//import javax.swing.*;
import java.util.Scanner;

//networking packages - listen on network for auth codes
//first up: read text file to get "network string"
//parse text of auth codes: separate "text" from random#
class receiverapp
{
  public static void main(String[] args)
  {
    System.out.println("Receiver App!");
    //generate what will be the passed "network string"
    String inputString = "1234approve-5678approve 10 min-4321deny";
    System.out.println(inputString);
    //parse string, separate into auth strings and option text strings
    String[] codes = inputString.split("-");
    //print strings
    System.out.println("Login request from a real site!");
    System.out.println("Select One:");
    for(int i=0; i<codes.length; i++)
    {
      String codeOut = codes[i].substring(4); //cuts out response code
      System.out.println((i + 1) + ". " + codeOut);
    }
    //request input for which option to select
    Scanner responseReq = new Scanner(System.in);
    int response = responseReq.nextInt();
    //based on input, output response code
    System.out.println(codes[response-1].substring(0,4));
  }
}
