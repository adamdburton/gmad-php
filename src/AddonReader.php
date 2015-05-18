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
        $this->buffer = new BinaryReader\BinaryReader(fopen($filename, 'r'));
    }
    
    public function parse()
    {
        if($this->buffer->readString(4) != Addon::ident)
        {
            return false;
        }
        
        $this->version = $this->buffer->readInt8(); // char
        
        if($this->version > Addon::version)
        {
            return false;
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
            
            $this->index[] = $file;
            
            $offset += $file['size'];
            $fileNumber++;
        }
        
        $this->fileBlock = $this->buffer->getPosition();
        
        return true;
    }

    public function getFile($fileID)
    {
        foreach($this->index as $file)
        {
            if($file['fileNumber'] == $fileID)
            {
                return $file;
            }
        }

        return false;
    }

    public function readFile($fileID)
    {
        if(!$file = $this->getFile($fileID)) { return false; }

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
            
            if($char == 0x00)
            {
                break;
            }
            
            $str .= chr($char);
        }
        
        return $str;
    }
}