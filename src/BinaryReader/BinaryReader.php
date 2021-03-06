<?php namespace AdamDBurton\GMad\BinaryReader;

class BinaryReader extends \PhpBinaryReader\BinaryReader
{
    /**
     * @var \AdamDBurton\GMad\PhpBinaryReader\Type\Int64
     */
    private $int64Reader;

    /**
     * @param  string|resource           $input
     * @param  int|string                $endian
     * @throws \InvalidArgumentException
     */
    public function __construct($input, $endian = \PhpBinaryReader\Endian::ENDIAN_LITTLE)
    {
        parent::__construct($input, $endian);

        $this->int64Reader = new Type\Int64();
    }

    /**
     * @return int
     */
    public function readInt64()
    {
        return $this->int64Reader->readSigned($this);
    }

    /**
     * @return int
     */
    public function readUInt64()
    {
        return $this->int64Reader->read($this);
    }

    /**
     * @return \PhpBinaryReader\Type\Int64
     */
    public function getInt64Reader()
    {
        return $this->int64Reader;
    }
}