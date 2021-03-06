<?php


class ProfileService implements UploadInterface
{
    private $pdo;
    private $profile;
    private $messageService;
    private $user;

    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
        $this->profile = new Profile();
        $this->messageService = new MessageService();
        $this->user = new User();

    }

    public function CheckUpload($upfile = null){
        $this->CheckSize();
        $this->CheckFormat();
        $this->CheckIfRealImage();
        $this->CheckIfExists();
    }

    public function CheckSize($upfile = null){
        if ( $this->profile->getSize() > $this->profile->getMaxSize() )
        {
            $this->messageService->AddMessage("Bestand " . $this->profile->getOrigineleNaam() . " is te groot (" . $this->profile->getSize() . " bytes). Maximum " . $this->profile->getMaxSize() . " bytes!", "error");
            $this->profile->setReturnvalue(false);
        }
    }

    public function CheckFormat($upfile = null) {
        //toegelaten extensies
        if ( ! in_array( pathinfo($this->profile->getOrigineleNaam(), PATHINFO_EXTENSION), $this->profile->getAllowedExtensions() ))
        {
            $this->messageService->AddMessage("Bestand " . $this->profile->getOrigineleNaam() . ": verkeerde bestandsextensie!", "error");
            $this->profile->setReturnvalue(false);
        }
    }

    public function CheckIfRealImage($upfile = null) {
        //is het bestand wel echt een afbeelding?
        if ( getimagesize($this->profile->getTmpName()) === false)
        {
            $this->messageService->AddMessage("Bestand " . $this->profile->getOrigineleNaam() . " is niet echt een afbeelding!", "error");
            $this->profile->setReturnvalue(false);
        }
    }

    public function CheckIfExists($upfile = null) {
        //bestaat het bestand al?
        if ( file_exists($this->profile->getTarget()) )
        {
            $this->messageService->AddMessage("Bestand " . $this->profile->getOrigineleNaam() . "bestaat al!", "error");
            $this->profile->setReturnvalue(false);
        }
    }

    public function SaveProfile() {
        global $Container;
        $this->user->setId($_SESSION['data'][0]['usr_id']);

        $images = $this->profile->getImages();
        $imagesql = "";

        foreach ( $images['profile'] as $image => $path ){
            $imagesql .= $image . " = '" . $path . "' ,";
        }
        $imagesql = substr($imagesql, 0, strlen($imagesql)-1);

        $sql = "update users SET " . $imagesql . " where usr_id=" . $this->user->getId();

        $Container->getPDOtoExecute($sql);
    }

    public function GetUserDataFromDatabase(){
        global $Container;

        $this->user->setId($_SESSION['data'][0]['usr_id']);


        //gebruikersgegevens ophalen uit databank
        $sql = "select * from users where usr_id=" .$this->user->getId();

        $data = $Container->getPDOData($sql);

        print "<table class='table table-striped table-bordered'>";
        foreach( $data as $row )
        {
            foreach( $row as $field => $value )
            {
                $notintable = false;

                //foto's afhandelen
                if ( $field == "usr_pasfoto" AND $value > "" ) { $this->profile->setImgPasfoto("<img class='thumbnail' src=img/$value>"); $notintable = true; }
                if ( $field == "usr_vz_eid" AND $value > "" ) { $this->profile->setImgVzEid("<img class='thumbnail' src=img/$value>"); $notintable = true; }
                if ( $field == "usr_az_eid" AND $value > "" ) { $this->profile->setImgAzEid("<img class='thumbnail' src=img/$value>"); $notintable = true; }

                //password niet tonen
                if ( $field == "usr_paswd" ) $notintable = true;

                //alle andere velden weergeven
                if ( !$notintable )
                {
                    $caption = str_replace("usr_", "", $field);
                    $caption = strtoupper(substr($caption,0,1)) . substr($caption,1);
                    print "<tr><td>$caption</td><td>$value</td></tr>";
                }
            }
            $this->profile->setImages();
        }
        print "</table>";
    }

    public function GetProfileImages(){
        return $this->profile->getImages();
    }

    public function ProcessUpload(){
        //pasfoto, eid_voorzijde en eid_achterzijde overlopen

        $this->user->setId($_SESSION['data'][0]['usr_id']);

        foreach ( $_FILES as $inputname => $fileobject )
        {
            $this->profile->LoadImageInfo($fileobject);
            $this->CheckUpload();

            if ( $this->profile->isReturnvalue() )
            {
                switch ( $inputname )
                {
                    case "pasfoto":
                        $this->profile->setTarget("pasfoto_" . $this->user->getId() . "." . $this->profile->getExtensie());
                        $this->profile->setImgPasfoto($this->profile->getTarget());
                        break;
                    case "eidvoor":
                        $this->profile->setTarget("eidvoor_" . $this->user->getId() . "." . $this->profile->getExtensie());
                        $this->profile->setImgVzEid($this->profile->getTarget());
                        break;
                    case "eidachter":
                        $this->profile->setTarget("eidachter_" . $this->user->getId() . "." . $this->profile->getExtensie());
                        $this->profile->setImgAzEid($this->profile->getTarget());
                        break;
                }

                $this->profile->setImages();
                $this->profile->setTarget($this->profile->getTargetDir() . $this->profile->getTarget());

                //bestand verplaatsen naar definitieve locatie
                $this->messageService->AddMessage("Moving " . $inputname . " to " . $this->profile->getTarget());

                if ( move_uploaded_file( $this->profile->getTmpName(), $this->profile->getTarget()))
                {
                    $this->messageService->AddMessage("Bestand " . $this->profile->getOrigineleNaam() . " opgeladen");
                }
                else $this->messageService->AddMessage("Sorry, there was an unexpected error uploading file " . $this->profile->getOrigineleNaam());
            }
        }
    }


}