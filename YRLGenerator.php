<?php

/*

  $arData = array(
  'type' => '',
  'property-type' => '',
  'category' => '',
  'url' => '',
  'payed-adv' => '',
  'manually-added' => '',
  'creation-date' => '',
  'last-update-date' => '',
  'expire-date' => '',
  'location' => array(
  'country' => '',
  'locality-name' => '',
  'sub-locality-name' => '',
  'address' => '',
  'metro' => array(
  array(
  'name' => '',
  'time-on-foot' => '',
  ),
  array(
  'name' => '',
  'time-on-foot' => '',
  ),
  ),
  ),
  'image' => array(

  ),
  'price' => array(
  'value' => '',
  'currency' => '',
  'period' => '',
  ),
  'description' => '',
  'area' => array(
  'value' => '',
  'unit' => '',
  ),
  'rooms' => '',
  'floor' => '',
  );

 */

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

    protected function writeLocationElement($arElements) {
        $engine = $this->getEngine();
        $engine->startElement('location');
        $this->writeElement($arElements);
        $engine->fullEndElement();
    }

    protected function writePriceElement($arElements) {
        $engine = $this->getEngine();
        $engine->startElement('price');
        $this->writeElement($arElements);
        $engine->fullEndElement();
    }

    protected function writeAreaElement($arElements) {
        $engine = $this->getEngine();
        $engine->startElement('area');
        $this->writeElement($arElements);
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

    protected function writeSales_agentElement($arElements) {
        $engine = $this->getEngine();
        $engine->startElement('sales-agent');
        $this->writeElement($arElements);
        $engine->fullEndElement();
    }

    protected function writeElement($arElements) {
        $engine = $this->getEngine();
        foreach ($arElements as $element => $value) {
            if (method_exists($this, 'write' . $this->convertStrToMethodName($element) . 'Element')) {
                $this->{'write' . $this->convertStrToMethodName($element) . 'Element'}($value);
            } else {
                if (!is_array($value)) {
                    $value = array($value);
                }
                foreach ($value as $val) {
                    $engine->writeElement($element, $val);
                }
            }
        }
    }

    private function convertStrToMethodName($str) {
        $name = str_replace('-', '_', $str); 

        return ucfirst($name);
    }

    abstract protected function offers();
}
