<?php

class Query_Controller extends Base_Controller {

    private $collection;


    public function __construct()
    {
        parent::__construct();
        $this->collection = new Collection();
    }

	public function action_md5($md5)
    {
        return $this->collection->getMd5($md5);
	}

    public function action_crc32($crc32)
    {
        return $this->collection->getCrc32($crc32);
    }

    public function action_sha1($sha1)
    {
        return $this->collection->getSha1($sha1);
    }

    public function action_sha256($sha256)
    {
        return $this->collection->getSha256($sha256);
    }

    public function action_set()
    {
        return $this->collection->set(Input::all());
    }
    public function action_retrieveCuesheet($sheetId)
    {
        return $this->collection->retreiveCuesheet($sheetId);
    }

}
