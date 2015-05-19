<?php namespace AdamDBurton\GMad;

class AddonWriter
{
    private $filename;
    private $buffer;
    
    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->buffer = fopen($filename, 'w');
    }
    
    public function write($folder, $files, $title, $description)
    {
        $folder = rtrim($folder, '/') . '/';

        $int8 = 'c'; // signed char (8)
        $int32 = 'i'; // signed integer (32)
        $uint32 = 'I'; // unsigned integer (32)
        $uint64 = 'Q'; // unsigned long long (64)

        $this->writeString(Addon::ident, false); // Ident (4)
        $this->writeInt(Addon::version, $int8); // Version (1)
        $this->writeInt(0, $uint64); // SteamID (64) [unused]
        $this->writeInt(time(), $uint64); // TimeStamp (64)
        $this->writeInt(0, $int8); // Required content, ignored

        $this->writeString($title); // Addon Name (n)
        $this->writeString($description); // Addon Description (n)

        $this->writeString('Author Name'); // Addon Author (n) [unused]
        $this->writeInt(1, $int32); // Addon Version (32) [unused]

        // File list

        $fileNumber = 0;

        foreach($files as $file)
        {
            $file = ltrim($file, '/');

            $crc = crc32($folder . $file);
            $size = filesize($folder . $file);

            $fileNumber++;

            $this->writeInt($fileNumber, $uint32); // File number (32)
            $this->writeString(strtolower($file)); // File name (lowercase) (n)
            $this->writeInt($size, $uint64); // File size (64)
            $this->writeInt($crc, $uint32); // File crc (32)
        }

        $this->writeInt(0, $uint32);

        foreach($files as $file)
        {
            $file = ltrim($file, '/');

            $contents = file_get_contents($folder . $file);

            $this->writeString($contents);
        }

        $this->writeInt(crc32($this->filename), $uint32);

        fclose($this->buffer);

        return true;
    }

    private function writeString($string, $null = true)
    {
        fwrite($this->buffer, $string);

        if($null)
        {
            fwrite($this->buffer, "\0"); // Null terminated string
        }

        return true;
    }

    public function writeInt($int, $mod)
    {
        fputs($this->buffer, pack($mod, $int));

        return true;
    }
}