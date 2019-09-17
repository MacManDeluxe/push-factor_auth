//import whatever I need
//networking packages - listen on network for auth codes
//first up: read text file to get "network string"
//parse text of auth codes: separate "text" from random#
class receiverapp
{
  public static void main(String args[])
  {
    System.out.println("Receiver App!");
    //generate what will be the passed "network string"
    String inputString = "1234approve1234deny";
    System.out.println(inputString);
    //parse string, separate into auth strings and option text strings
    //print strings
    //request input for which option to select
    //based on input, cat auth string onto response string and save to file
  }
}
