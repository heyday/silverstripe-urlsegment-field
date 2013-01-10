<?php

class URLSegment extends DBField implements CompositeDBField
{

    protected $original;
    protected $compiled;

    static $composite_db = array(
        'Original' => 'Varchar(255)',
        'Compiled' => 'Varchar(255)'
    );

    function compositeDatabaseFields() {
        return self::$composite_db;
    }

    function requireField() {
        $fields = $this->compositeDatabaseFields();
        if($fields) foreach($fields as $name => $type){
            DB::requireField($this->tableName, $this->name . $name, $type);
        }
    }

    function writeToManipulation(&$manipulation) {
        $original = $this->getOriginal();
        if($original) {
            $manipulation['fields'][$this->name.'Original'] = $this->prepValueForDB($original);
            $manipulation['fields'][$this->name.'Compiled'] = $this->prepValueForDB($this->getCompiled($original));
        } else {
            $manipulation['fields'][$this->name.'Original'] = DBField::create('Varchar', $this->getOriginal())->nullValue();
            $manipulation['fields'][$this->name.'Compiled'] = DBField::create('Varchar', '')->nullValue();
        }

    }

    function addToQuery(&$query) {
        parent::addToQuery($query);
        $query->selectField(sprintf('"%Original"', $this->name));
        $query->selectField(sprintf('"%Compiled"', $this->name));
    }

    public function getOriginal()
    {
        return $this->original;
    }

    public function getCompiled($value = null)
    {
        if (!is_null($value)) {
            $t = (function_exists('mb_strtolower')) ? mb_strtolower($value) : strtolower($value);
            $t = Object::create('Transliterator')->toASCII($t);
            $t = str_replace('&amp;','-and-',$t);
            $t = str_replace('&','-and-',$t);
            $t = ereg_replace('[^A-Za-z0-9]+','-',$t);
            $t = ereg_replace('-+','-',$t);
            $t = trim($t, '-');
            return $t;
        }
        return $this->compiled;
    }

    public function setOriginal($original, $markChanged = true) {
        $this->original = (string)$original;
        if($markChanged) $this->isChanged = true;
    }

    public function setCompiled($compiled, $markChanged = true) {
        $this->compiled = (string)$compiled;
        if($markChanged) $this->isChanged = true;
    }

    function setValue($value, $record = null, $markChanged = true) {
        if ($value instanceof URLSegment && $value->exists()) {
            $this->setOriginal($value->getOriginal(), $markChanged);
            $this->setCompiled($value->getCompiled(), $markChanged);
            if($markChanged) $this->isChanged = true;
        } else if($record && isset($record[$this->name . 'Original'])) {
            $this->setOriginal($record[$this->name . 'Original']);
            if (array_key_exists($this->name . 'Compiled', $record)) {
                $this->setCompiled($record[$this->name . 'Compiled']);
            }
            if($markChanged) $this->isChanged = true;
        } else if (is_array($value)) {
            if (array_key_exists('Original', $value)) {
                $this->setOriginal($value['Original'], $markChanged);
            }
            if (array_key_exists('Compiled', $value)) {
                $this->setCompiled($value['Compiled'], $markChanged);
            }
            if($markChanged) $this->isChanged = true;
        } elseif(is_string($value)) {
            $this->setOriginal($value, $markChanged);
        }
    }

    /**
     * @return string
     */
    function Nice($options = array()) {
        return $this->getOriginal();
    }

    /**
     * @return boolean
     */
    function exists() {
        return $this->getOriginal();
    }

    function isChanged() {
        return $this->isChanged;
    }

    public function scaffoldFormField($title = null) {
        return new URLSegmentField($this->name, $title);
    }

}
