<?php namespace AdamDBurton\GMad;

class AddonReader
{
    private $version;
    private $steamid;
    private $timestamp;

    private $addonName;
    private $addonVersion;
    private $addonAuthor;
    private $addonDescription;
    private $addonType;
    private $addonTags;

    private $index;
    private $fileBlock;
    private $buffer;

    public function __construct($filename)
    {
        $fileResource = fopen($filename, 'r');
        $this->buffer = new BinaryReader\BinaryReader($fileResource);

        return true;
    }

    public function parse()
    {
        if($this->buffer->readString(1) != 'G' || $this->buffer->readString(1) != 'M' || $this->buffer->readString(1) != 'A' || $this->buffer->readString(1) != 'D')
        {
            return false;
        }

        $this->version = $this->buffer->readInt8();

        // Check version here

        $this->steamid = $this->buffer->readInt64(); // steamid
        $this->timestamp = $this->buffer->readInt64(); // timestamp

        // Required content (not used at the moment, just read out)

        if($this->version > 1)
        {
            $content = $this->readString();

            while($content !== '')
            {
                $content = $this->readString();
            }
        }

        $this->addonName = $this->readString();
        $this->addonDescription = $this->readString();
        $this->addonAuthor = $this->readString();

        // Addon version - unused

        $this->addonVersion = $this->buffer->readInt16();

        $this->fileBlock = $this->buffer->getPosition();

        // File index

        $offset = 0;
        $fileNumber = 1;

        while($this->buffer->readUInt16() != 0)
        {
            $file = [
                'name' => $this->readString(),
                'size' => $this->buffer->readInt32(),
                'crc' => $this->buffer->readUInt16(),
                'offset' => $offset,
                'fileNumber' => $fileNumber
            ];

            $this->index[] = $file;

            $offset += $file['size'];
            $fileNumber++;
        }

        //$this->fileBlock = $this->buffer->getPosition();

        // Parse json data

        $json = json_decode($this->addonDescription);

        if($json)
        {
            $this->addonDescription = $json->description;
            $this->addonType = $json->type;
            $this->addonTags = $json->tags;
        }

        return true;
    }

    private function readString()
    {
        // Replacement for ReadString that reads until 0x00

        $str = '';

		while(true)
        {
            if(!$this->buffer->canReadBytes(1))
            {
                break;
            }

            $char = $this->buffer->readInt8();

			if($char == 0x00)
            {
                break;
            }

			$str .= chr($char);
		}

		return $str;
    }
}