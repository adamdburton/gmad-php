<?php namespace AdamDBurton\GMad;

use PhpBinaryReader\BinaryReader;

class AddonReader
{
    private $name;
    private $author;
    private $description;
    private $version;
    private $type;
    private $tags;

    private $index;
    private $fileBlock;
    private $buffer;

    public function __construct($filename)
    {
        $this->clear();

        $fileResource = fopen($filename, 'r');
        $this->buffer = new BinaryReader($fileResource);

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

        $this->buffer->readInt32(); $this->buffer->readInt32(); // steamid
        $this->buffer->readInt32(); $this->buffer->readInt32(); // timestamp

        // Required content (not used at the moment, just read out)

        if($this->version > 1)
        {
            $content = $this->readString();

            while($content != '')
            {
                $content = $this->readString();
            }
        }

        $this->name = $this->readString();
        $this->description = $this->readString();
        $this->author = $this->readString();

        // Addon version - unused

        $this->buffer->readInt16();

        // File index

        $fileNumber = 1;
        $offset = 0;

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

        $this->fileBlock = $this->buffer->getPosition();

        $json = json_decode($this->description);

        if($json)
        {
            $this->description = $json->description;
            $this->type = $json->type;
            $this->tags = $json->tags;
        }

        return true;
    }

    private function readString()
    {
        // Replacement for ReadString that reads until 0x00;

        $str = '';

		while(true)
        {
            if(!$this->buffer->canReadBytes(1))
            {
                break;
            }

            $char = $this->buffer->readString(1);

			if($char == 0x00)
            {
                break;
            }

			$str .= $char;
		}

		return $str;
    }

    private function readInt64()
    {

    }

    private function clear()
    {
        $this->version = 0;
        $this->name = '';
        $this->author = '';
        $this->description = '';
        $this->type = '';
        $this->tags = [];

        $this->index = [];
        $this->fileBlock = 0;
        $this->buffer = new BinaryReader('');
    }
}