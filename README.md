# push-factor_auth
## Insight Security 2019c Project

## Introduction 

Push-Factor_Auth is a proof-of-concept for an improvement over mainstream push-based two-factor authentication systems. While push-based 2fa provides the easiest experience for end-users, it is SO easy that victims may instinctively or accidentally authenticate their attacker. For such situations, I have implemented an "Oh no, cancel!" button within the app that deletes the authenticated session server-side.

In addition, there are situations where a single "approve all-access" is not desirable. Rather than a blanket approve/deny, the app allows websites to send auth codes with multiple levels of approval. If a user is accessing their account via a shared computer, they may wish to only approve a session that lasts no more than 10 minutes. When the user selects this option via the app, they are automatically logged out after the selected amount of time.

Each auth option sent by the website is preceeded by a randomly generated code, which the app will send back when the option is selected. Because each code is the same length, and different every time, an eavesdropper will have no insight into which option was selected when both the input string and output code are encrypted (AES-256 encryption, with keys shared via elliptic curve cryptography once during account setup, will be implemented in a future update).
example test input String:
"1234Approve-5678Approve 10 min-4321Deny-8756Cancel Login-sessionID"

Because the website determines the quantity and content of the codes that are sent, the system is flexible enough to allow for any number of approval levels, so long as the website is built to enforce them. A banking website, for example, might implement a "read-only" approval level for users who only wish to check their balance.

## Installation

The Java receiverapp is within an IntelliJ evnironment folder.
Source code: /push-factor_auth/PushFactorAuth/src/main/java/receiverapp.java
Compiled class: /push-factor_auth/PushFactorAuth/build/classes/java/main/receiverapp.class

Run the app from the terminal:
java receiverapp

For testing purposes, once the receiverapp is running, the actions of the website can be simulated using the following bash script on MacOS:
/push-factor_auth/src/auth2factor.sh

To run the website and associated MySQL user database on localhost in MacOS, I recommend downloading MAMP and setting the Web Server Document Root to .../push-factor_auth/src/user_login_session in MAMP Preferences. Set the apache port to 8888, and the MySQL port to 8889. The sql schema is contained in /push-factor_auth/src/user_login_session/sql/schema.sql
You will have to replace the user password field with an md5 hash of the password of your choice. While not secure (the PHP fuctions password_hash() and password_verify() are recommended), this was a usability tradeoff to make a code reviewer's life easier until an account 2fa setup process is implemented.

Once everything is set up:
1. Run the java receiverapp from the terminal
2. In MAMP, start the apache and MySQL servers
3. Direct a browser to http://localhost:8888/index.php
4. Attempt login using the password you hashed into the database
