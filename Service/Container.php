<?php


class Container
{
    private $configuration;
    private $pdo;
    private $cityhandler;
    private $messageService;
    private $userLoader;
    private $upload;
    private $download;

    public function __construct(array $configuration){
        $this->configuration = $configuration;
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

    /**
     * @return CityLoader
     */
    public function getCityHandler(){
        if ($this->cityhandler === null) $this->cityhandler = new CityLoader($this->getPDO());
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
     * @return Upload
     */
    public function getUpload(){
        if ($this->upload === null) $this->upload = new Upload($this->getPDO());
        return $this->upload;
    }

    /**
     * @return Download
     */
    public function getDownload(){
        if($this->download === null) $this->download = new Download($this->getPDO());
        return $this->download;
    }

}