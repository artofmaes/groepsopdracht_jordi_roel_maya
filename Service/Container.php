<?php


class Container
{
    private $configuration;
    private $pdo;
    private $cityhandler;
    private $messageService;
    private $pageLoader;
    private $userLoader;
    private $uploadService;
    private $download;
    private $profileService;
    private $countryHandler;

    public function __construct(array $configuration){
        $this->configuration = $configuration;
    }

    public function getPageLoader(){
        if ($this->pageLoader === null){
            $this->pageLoader = new PageLoader();
        }

        return $this->pageLoader;
    }

    /**
     * @return PDO
     */
    public function getPDO(){
        if ($this->pdo === null){
            $this->pdo= new PDO(
                $this->configuration['db_dsn'],
                $this->configuration['db_user'],
                $this->configuration['db_pass']
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->pdo;
    }


    public function getPDOData($sql){
        $pdo = $this->getPDO();

        $stm = $pdo->prepare($sql);
        $stm->execute();

        $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    /**
     * @param $sql
     * @return bool
     */
    public function getPDOtoExecute($sql){
        $pdo = $this->getPDO();

        $stm = $pdo->prepare($sql);

        if ( $stm->execute() ) return true;
        else return false;
    }

    /**
     * @return CityHandler
     */
    public function getCityHandler(){
        if ($this->cityhandler === null) $this->cityhandler = new CityHandler($this->getPDO());
        return $this->cityhandler;
    }

    /**
     * @return MessageService
     */
    public function getMessageService(){
        if($this->messageService === null) $this->messageService = new MessageService();
        return $this->messageService;
    }

    /**
     * @return UserLoader
     */
    public function getUserLoader(){
        if($this->userLoader === null) $this->userLoader = new UserLoader($this->getPDO());
        return $this->userLoader;
    }

    /**
     * @return UploadService
     */
    public function getUploadService(){
        if ($this->uploadService === null) $this->uploadService = new UploadService($this->getPDO());
        return $this->uploadService;
    }

    /**
     * @return Download
     */
    public function getDownload(){
        if($this->download === null) $this->download = new Download($this->getPDO());
        return $this->download;
    }

    /**
     * @return ProfileService
     */
    public function getProfileService()
    {
        if($this->profileService === null ) $this->profileService = new ProfileService($this->getPDO());
        return $this->profileService;
    }

    /**
     * @return mixed
     */
    public function getCountryHandler()
    {
        if($this->countryHandler === null ) $this->countryHandler = new CountryHandler($this->getPDO());
        return $this->countryHandler;
    }


}