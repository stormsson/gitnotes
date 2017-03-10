<?php
namespace SpreadsheetManager;

use Google\Spreadsheet as Spreadsheet;

class XlsManager {
    protected $googleClient;
    protected $spreadsheet;
    protected $worksheets;
    protected $currentWorksheet;

    protected $columnNames=[];

    public function __construct($credentialPath) {
        if(!is_readable($credentialPath)) {
            throw new \Exception("Google credential file is not readable", 1);
        }

        putenv('GOOGLE_APPLICATION_CREDENTIALS='.$credentialPath);

        $this->googleClient = new \Google_Client;
        $this->googleClient->useApplicationDefaultCredentials();

        $this->googleClient->setApplicationName("Github Notes Tracker");
        $this->googleClient->setScopes([
            'https://www.googleapis.com/auth/drive',
            'https://spreadsheets.google.com/feeds'
        ]);

        if ($this->googleClient->isAccessTokenExpired()) {
            $this->googleClient->refreshTokenWithAssertion();
        }

        $accessToken = $this->googleClient->fetchAccessTokenWithAssertion()["access_token"];
        Spreadsheet\ServiceRequestFactory::setInstance(
            new Spreadsheet\DefaultServiceRequest($accessToken)
        );
    }

    protected function setColumnNames() {
        $listFeed = $this->currentWorksheet->getListFeed();
        $firstRow = $listFeed->getEntries()[0];

        $this->columnNames = array_keys($firstRow->getValues());

    }

    public function open($name) {
        $this->spreadsheet = (new Spreadsheet\SpreadsheetService)
           ->getSpreadsheetFeed()
           ->getByTitle($name);

        $this->worksheets = $this->spreadsheet->getWorksheetFeed()->getEntries();
        $this->currentWorksheet = $this->worksheets[0];

        $this->setColumnNames();
    }

    public function insertOrUpdate($data) {


        $data = array_combine($this->columnNames, $data);

        $listFeed = $this->currentWorksheet->getListFeed([
            "sq" => "repositoryowner={$data['repositoryowner']} AND repositoryname={$data['repositoryname']} "
        ]);
        $title = strtolower($data['titolo']);

        /** @var ListEntry */
        foreach ($listFeed->getEntries() as $entry) {
            $row = $entry->getValues();
            if(strtolower($row['titolo']) == $title && (strtolower($row['filename']) == $data['filename'])) {
                return $entry->update(array_merge($entry->getValues(), $data));
           }
        }

        return $this->insert(array_values($data));
    }


    public function insert($data) {
        $data = array_combine($this->columnNames, $data);
        $listFeed = $this->currentWorksheet->getListFeed();
        $listFeed->insert($data);
    }

    public function debug() {
        $listFeed = $this->currentWorksheet->getListFeed();

        /** @var ListEntry */
        foreach ($listFeed->getEntries() as $entry) {
           $row = $entry->getValues();
           var_dump($row);
        }
    }
}