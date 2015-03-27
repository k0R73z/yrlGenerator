<?php

abstract class YRLGenerator extends CApplicationComponent {

    /**
     * Xml file encoding
     * @var string
     */
    public $encoding = 'utf-8';

    /**
     * Output file name. If null 'php://output' is used.
     * @var string
     */
    public $outputFile;

    /**
     * Indent string in xml file. False or null means no indent;
     * @var string
     */
    public $indentString = "\t";
    protected $_dir;
    protected $_file;
    protected $_tmpFile;
    protected $_engine;

    public function run() {
        $this->beforeWrite();
        $this->writeOffers();
        $this->afterWrite();
    }

    protected function getEngine() {
        if (null === $this->_engine) {
            $this->_engine = new XMLWriter();
        }
        return $this->_engine;
    }

    protected function beforeWrite() {
        if ($this->outputFile !== null) {
            $slashPos = strrpos($this->outputFile, DIRECTORY_SEPARATOR);
            if (false !== $slashPos) {
                $this->_file = substr($this->outputFile, $slashPos);
                $this->_dir = substr($this->outputFile, 0, $slashPos);
            } else {
                $this->_dir = ".";
            }
            $this->_tmpFile = $this->_dir . DIRECTORY_SEPARATOR . md5($this->_file);
        } else {
            $this->_tmpFile = 'php://output';
        }
        $engine = $this->getEngine();
        $engine->openURI($this->_tmpFile);
        if ($this->indentString) {
            $engine->setIndentString($this->indentString);
            $engine->setIndent(true);
        }
        $engine->startDocument('1.0', $this->encoding);
        $engine->startElement('realty-feed');
        $engine->writeAttribute('xmlns', 'http://webmaster.yandex.ru/schemas/feed/realty/2010-06');
        $engine->writeElement('generation-date', date('Y-m-d\TH:i:s+03:00')); // YYYY-MM-DDTHH:mm:ss+00:00
    }

    protected function afterWrite() {
        $engine = $this->getEngine();
        $engine->fullEndElement();
        $engine->endDocument();

        if (null !== $this->outputFile)
                rename($this->_tmpFile, $this->outputFile);
    }

    protected function writeOffers() {
        $this->offers();
    }

    protected function addOffer($id, $data) {
        $engine = $this->getEngine();
        $engine->startElement('offer');
        $engine->writeAttribute('internal-id', $id);
        $this->writeElement($data);
        $engine->fullEndElement();
    }


    protected function writeMetroElement($arElements) {
        $engine = $this->getEngine();

        if (is_array($arElements[0])) {
            foreach ($arElements as $metro) {
                $this->writeMetroElement($metro);
            }
        } else {
            $engine->startElement('metro');
            $this->writeElement($arElements);
            $engine->fullEndElement();
        }
    }

    protected function writeImageElement($arImages) {
        $engine = $this->getEngine();
        foreach ($arImages as $imageUrl) {
            $engine->writeElement('image', $imageUrl);
        }
    }
   
    protected function writeArrayElement($element, $arElements) {
        $engine = $this->getEngine();
        $engine->startElement($element);
        $this->writeElement($arElements);
        $engine->fullEndElement();
    }
   
    protected function writeElement($arElements) {
        $engine = $this->getEngine();
        foreach ($arElements as $element => $value) {
            $methodName = 'write' . $this->convertStrToMethodName($element) . 'Element';
            if (method_exists($this, $methodName)) {
                $this->{$methodName}($value);
            } elseif (is_array($value)) {
                $this->writeArrayElement($element, $value);
            } else {
                $engine->writeElement($element, $value);
            }
        }
    }

    private function convertStrToMethodName($str) {
        $name = str_replace('-', '_', $str);
        return ucfirst($name);
    }

    abstract protected function offers();
}
