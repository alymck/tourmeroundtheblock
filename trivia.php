<?php
header("content-type: text/xml");

session_start();

//Added this to try to ensure that sessions act correctly.
if (ini_get('register_globals'))
{
    foreach ($_SESSION as $key=>$value)
    {
        if (isset($GLOBALS[$key]))
            unset($GLOBALS[$key]);
    }
}

function welcome() {

  $_SESSION["name"] = $_POST["Body"];
  //reset session defaults
  $_SESSION["score"] = 0;
  $_SESSION["question_number"] = 0;
  return "So you went to Boomtown, ".$_SESSION["name"]."? Think you remember loads of stuff? And now you fancy your chances on the big bad Boomtown 2017 Observational Trivia Quiz? \n\nWell, let's have ya then. 15 Questions worth 100 points each, so the max score on this quiz is 1500. If you get stuck, text 'hint'... but beware: it'll cost you 50 points each time you do!\n\n";
}

function start_new_game() {
  //unset the variables
  session_unset(); 
  //destroy the session 
  session_destroy();
  //unset the persistent session variables
  unset($_SESSION["name"]); 
  unset($_SESSION['correct_answer']);
  unset($_SESSION['score']);
  return "Your request to start a new Boomtown quiz has been received. Please text back your name to begin. ".$_SESSION["name"];
}

function ask_question() {
  //convert the questions file into an array based on line breaks
  $lines = file('questions.txt', FILE_IGNORE_NEW_LINES);
  $line = $lines[$_SESSION["question_number"]];
  $q_and_a = explode("|", $line);
  $_SESSION["question_number"] = $q_and_a[0];;
  $_SESSION['correct_answer'] = $q_and_a[2];
  return $q_and_a[1];
  //update question number
  $_SESSION["question_number"] += 1;
}

function provide_hint() {
  //grab the hint from the txt file
  $lines = file('questions.txt', FILE_IGNORE_NEW_LINES);
  $line = $lines[$_SESSION["question_number"] -= 1];
  $q_and_a = explode("|", $line);
  $hint = $q_and_a[3];
  $line = $lines[$_SESSION["question_number"] += 1];
  $_SESSION['score'] -= 50;
  return $hint;
}

function respond_to_answer() {
  // convert the response string to all lowercase
  $clean_response = (strtolower($_POST['Body']));
  // strip out all response whitespace
  $clean_response = preg_replace('/\s*/', '', $clean_response);
  // convert the answer string to all lowercase
  $clean_answer = (strtolower(($_SESSION['correct_answer'])));
  // strip out all answer whitespace
  $clean_answer = preg_replace('/\s*/', '', $clean_answer);
  
if ($clean_response == $clean_answer){
    $_SESSION['score'] += 100;
    return "Yep, '".$_POST['Body']."' is the correct answer - you get 100 points.\n\n"; 
  } else { 
    return "Sorry, that's a fail. You said '".$_POST['Body']."' but the answer was '".$_SESSION['correct_answer']."'\n\n"; 
  }
}



if (strtolower($_POST['Body']) == "new game" || strtolower($_POST['Body']) == "new game ") {
  $message = start_new_game();
} else if (strtolower($_POST['Body']) == "hint" || strtolower($_POST['Body']) == "hint ") {
  $message = provide_hint();
} else if (!isset($_SESSION["name"])) {
  $message = welcome();
  $message .= ask_question();
} else {
  $message = respond_to_answer();
  $message .= ask_question();
  $message .= "\n\n(Your score is " . $_SESSION['score'] . " points)";
}

?>

<Response>
  <Message>
    <?= $message ?>
  </Message>
</Response>

