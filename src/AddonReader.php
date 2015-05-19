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
        try
        {
            $fp = fopen($filename, 'r');
        }
        catch(\Exception $e)
        {
            throw new Exceptions\FileNotFoundException($filename . ' could not be found');
        }

        $this->buffer = new BinaryReader\BinaryReader($fp);
    }
    
    public function parse()
    {
        if($this->buffer->readString(4) != Addon::ident)
        {
            throw new Exceptions\InvalidFormatException('GMA ident not found.');
        }

        $this->version = $this->buffer->readInt8(); // char
        
        if($this->version > Addon::version)
        {
            throw new Exceptions\InvalidVersionException('Cannot read version ' . $this->version . ' files.');
        }
        
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
        
        $this->addonVersion = $this->buffer->readInt32();

        // Parse json data

        $json = json_decode($this->addonDescription);

        if($json)
        {
            $this->addonDescription = $json->description;
            $this->addonType = $json->type;
            $this->addonTags = $json->tags;
        }

        // File index
        
        $offset = 0;
        $fileNumber = 1;
        
        while($this->buffer->readUInt32())
        {
            $file = [
                'name' => $this->readString(),
                'size' => $this->buffer->readInt64(),
                'crc' => $this->buffer->readUInt32(),
                'offset' => $offset,
                'fileNumber' => $fileNumber
            ];
            
            $this->index[$fileNumber] = $file;
            
            $offset += $file['size'];
            $fileNumber++;
        }
        
        $this->fileBlock = $this->buffer->getPosition();
        
        return true;
    }

    public function getName()
    {
        return $this->addonName;
    }

    public function getVersion()
    {
        return $this->addonVersion;
    }

    public function getAuthor()
    {
        return $this->addonAuthor;
    }

    public function getDescription()
    {
        return $this->addonDescription;
    }

    public function getType()
    {
        return $this->addonType;
    }

    public function getTags()
    {
        return $this->addonTags;
    }

    public function getFiles()
    {
        return $this->index;
    }

    public function getFileInfo($fileID)
    {
        if(isset($this->index[$fileID]))
        {
            return $this->index[$fileID];
        }

        throw new Exceptions\InvalidFileException('File ' . $fileID . ' no found in GMA');
    }

    public function getFile($fileID)
    {
        if(!$file = $this->getFileInfo($fileID)) { return false; }

        // Set start position

        $this->buffer->setPosition($this->fileBlock + $file['offset']);

        return $this->buffer->readBytes($file['size']);
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
            
            if($char == "\0")
            {
                break;
            }
            
            $str .= chr($char);
        }
        
        return $str;
    }
}