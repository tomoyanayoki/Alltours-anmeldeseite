<?php

define('SYSTEM_DIR', '../../system' . DIRECTORY_SEPARATOR);

require_once SYSTEM_DIR . 'autoload.php';
require_once(SYSTEM_DIR . 'maileon/maileon-php-client-1.5.4/client/MaileonApiClient.php');

$config = array(
    "BASE_URI" => "https://api.maileon.com/1.0",
    'API_KEY' => 'WlfkHbRR-0Z3sLK9qSnik6XJhzVGvUm4iLWl',
);

if (isset($_POST['email'])) {
    /* GET EMAIL */
    $email = htmlspecialchars(strip_tags(trim($_POST['email'])));
    $anrede = htmlspecialchars(strip_tags(trim($_POST['anrede'])));
    $firstname = htmlspecialchars(strip_tags(trim($_POST['vorname'])));
    $lastname = htmlspecialchars(strip_tags(trim($_POST['nachname'])));
    $birthday = htmlspecialchars(strip_tags(trim($_POST['geburtsdatum'])));
    $plz = htmlspecialchars(strip_tags(trim($_POST['plz'])));


    /* XQ:VERBINDUNGSAUFBAU */
    $contactsService = new com_maileon_api_contacts_ContactsService($config);

    /* XQ:KONTAKTANFRAGE */
    $getContact = $contactsService->getContactByEmail($email); // the second are the custom fields

    if ($getContact->isSuccess() && $getContact->getResult()->permission != com_maileon_api_contacts_Permission::$NONE) {
        $updatedContact = new com_maileon_api_contacts_Contact();
        $updatedContact->email = $email;
        $updatedContact->anonymous = false;

        $response = $contactsService->createContact($updatedContact, com_maileon_api_contacts_SynchronizationMode::$UPDATE, '', '');

        $resp = "Danke für Dein Interesse. Du bist bereits zu unserem Newsletter angemeldet.";
    } else {
        $newContact = new com_maileon_api_contacts_Contact();
        $newContact->email = $email;
        $newContact->anonymous = false;
        $newContact->permission = com_maileon_api_contacts_Permission::$NONE;

        if ($anrede) {

            $newContact->standard_fields["SALUTATION"] = $anrede;
            if ($anrede == "Frau") {
                $newContact->standard_fields["GENDER"] = "f";
            }
            if ($anrede == "Herr") {
                $newContact->standard_fields["GENDER"] = "m";
            }
            if ($anrede == "Divers") {
                $newContact->standard_fields["GENDER"] = "d";
            }
        }

        $newContact->standard_fields["FIRSTNAME"] = $firstname;
        $newContact->standard_fields["LASTNAME"] = $lastname;

        $birthday = explode(".", $birthday);
        $birthday = $birthday[2] . "-" . $birthday[1] . "-" . $birthday[0];
        $newContact->standard_fields["BIRTHDAY"] = $birthday;

        $newContact->standard_fields["ZIP"] = $plz;

        $newContact->custom_fields["Anmeldequelle"] = "Newsletterseite";
        $newContact->custom_fields["Gewinnspiel Teilnahme Webseite 2023"] = "true";
        $newContact->custom_fields["Herkunft"] = "alltoursde";

        $response = $contactsService->createContact($newContact, com_maileon_api_contacts_SynchronizationMode::$UPDATE, '', '', true, true, '1ocO2nRa');
        $resp = "Vielen Dank für Deine Anmeldung!";
    }
} else {
    $email = "";
}

$curlx = curl_init();

curl_setopt($curlx, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
curl_setopt($curlx, CURLOPT_HEADER, 0);
curl_setopt($curlx, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curlx, CURLOPT_POST, 1);

$post_data =
    [
        'secret' => '6LcOV4AjAAAAAJItmpPzepUFxN6WNoFMxoVsEAH4',
        'response' => $_POST['g-recaptcha-response']
    ];

curl_setopt($curlx, CURLOPT_POSTFIELDS, $post_data);

$resp = json_decode(curl_exec($curlx));

curl_close($curlx);

if ($resp->success) {
    //success!
} else {
    // failed
    echo "error";
    exit;
}
