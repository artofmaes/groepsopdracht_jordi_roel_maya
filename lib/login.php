<?php
$login_form = true;
require_once "autoload.php";

$formname = $_POST["formname"];
$buttonvalue = $_POST['loginbutton'];

if ( $formname == "login_form" AND $buttonvalue == "Log in" )
{
    $user = new User();
    $userLoader = $Container->getUserLoader();

    if ($userLoader->CheckLogin() )
    {
        $MS->AddMessage( "Welkom, " .$user->getVoornaam() . "!" );
        header("Location: " . $_application_folder . "/steden.php");
    }
    else
    {
        $MS->AddMessage( "Sorry! Verkeerde login of wachtwoord!", "error" );
        header("Location: " . $_application_folder . "/login.php");
    }
}
else
{
    $MS->AddMessage( "Foute formname of buttonvalue", "error" );
}
?>



