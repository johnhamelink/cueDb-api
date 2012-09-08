<?php

class Collection
{

    protected $conn;
    protected $db;
    protected $collection;
    protected $hostname = 'localhost';

    protected $whiteList = [
        'album',
        'artist',
        'crc32',
        'md5',
        'sha1',
        'sha256',
        'cuesheet'
    ];

    /**
     * Set up mongo connection object
     * Connect to DB and select collection
     */
    public function __construct()
    {
        $this->conn = new Mongo($this->hostname);
        $this->db = $this->conn->cuedb;
        $this->collection = $this->db->Collections;
    }

    /**
     * Disconnect from the DB
     */
    public function __destruct()
    {
        $this->conn->close();
    }

    /**
     * showResult
     *
     * Filter and clean the DB result and JSONify it
     * Ready for the user to read
     *
     * @param Array $resultSet
     *
     * @return String Json
     */
    protected function showResult($resultSet)
    {
        if ($resultSet) {
            $resultSet["id"] = (string)$resultSet["_id"];
            $resultSet["cuesheet"] = URL::to('query/cuesheet/' . (string)$resultSet["cuesheet"]);
            unset($resultSet["_id"]);
            return json_encode($resultSet);
        } else {
            return json_encode(new stdClass());
        }
    }

    /**
     *  sanitizeInput
     *
     *  This method removes any unfamiliar options from
     *  the POST params, and formats the data as a string.
     *
     *  @param Array $input
     *
     *  @return Array
     */
    protected function sanitizeInput($input)
    {
        foreach ($input as $key => $value)
        {
            if (!in_array($key,  $this->whiteList)) {
                unset($input[$key]);
                continue;
            }

            filter_var($input[$key], FILTER_SANITIZE_STRING);
        }
        return $input;
    }

    /**
     * validateInput
     *
     * This method makes sure we have the minimum amount
     * of data we need in order to continue.
     *
     * @param Array $input
     *
     * @param Bool
     *
     */
    protected function validateInput($input)
    {
        foreach ($input as $key => $value)
        {
            $inputElms[] = $key;
        }

        foreach ($this->whiteList as $list) {
            if (!in_array($list, $inputElms)) {
                return false;
            }
        }

        return true;
    }

    public function getMd5($md5)
    {
        $result = $this->collection->findOne([ 'md5' => $md5 ]);
        return $this->showResult($result);
    }

    public function getSha1($sha1)
    {
        $result = $this->collection->findOne([ 'sha1' => $sha1 ]);
        return $this->showResult($result);
    }

    public function getSha256($sha256)
    {
        $result = $this->collection->findOne([ 'sha256' => $sha256 ]);
        return $this->showResult($result);
    }

    public function getCrc32($crc32)
    {
        $result = $this->collection->findOne([ 'crc32' => $crc32 ]);
        return $this->showResult($result);
    }

    /**
     * retrieveCuesheet
     *
     * This gets a cuesheet from gridFS by
     * searching by ID.
     *
     * @param String $sheetId
     *
     * @return String
     */
    public function retreiveCuesheet($sheetId)
    {
        $grid  = $this->db->getGridFS();
        $sheet = $grid->findOne(['_id' => new MongoId($sheetId)]);
        if($sheet) {
            header('Content-type: ' . $sheet->file['metadata']['contentType']);
            header('Content-Disposition: attachment; filename="' . $sheet->file['metadata']['filename'] . '"');
            return $sheet->getBytes();
        }
    }

    /**
     * storeCuesheet
     *
     * This method saves a file into the DB and removes it
     * from the HDD.
     *
     * @return String
     */
    protected function storeCuesheet()
    {
        $file = Input::file('cuesheet');

        $grid = $this->db->getGridFS();
        return $grid->storeFile(
                $file['tmp_name'],
                [
                    'metadata' => [
                        ['filename'   => $file['name']],
                        'filename'    => $file['name'],
                        'contentType' => $file['type']
                    ]
                ]
        );
    }

    /**
     * set
     *
     * This method saves the cuesheet info into
     * the DB, if it is safe and not a duplicate.
     *
     * @param array $input
     *
     * @return String
     */
    public function set(array $input)
    {
        $result = new stdClass();
        $result->success = false;

        $input = $this->sanitizeInput($input);
        if ($this->validateInput($input)) {
            $exists = $this->collection->findOne([ 'crc32' => $input['crc32'] ]);

            if (!$exists) {
                $this->collection->ensureIndex(['md5' => 1, 'sha1' => 1, 'sha256' => 1, 'crc32' => 1], ['unique' => 1, 'dropDups' => 1, 'background' => true]);
                $input['cuesheet'] = $this->storeCuesheet($input['cuesheet']);
                $this->collection->insert($input);

                $result->success = true;
                $result->message = "Successfully added the cuesheet for {$input['artist']} - {$input['album']}";
            } else {
                $result->message = "This entry already exists in the Database.";
            }
        } else {
            $result->message = "Invalid input.";
        }

        return json_encode($result);
    }


}
