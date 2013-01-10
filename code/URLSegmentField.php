<?php

class URLSegmentField extends TextField
{

    function saveInto(DataObjectInterface $dataObject)
    {
        $fieldName = $this->name;
        if ($dataObject->$fieldName && $dataObject->$fieldName->hasMethod('setOriginal')) {
            $dataObject->$fieldName->setOriginal($this->Value(), true);
        }
    }

    function setValue($val)
    {
        if (is_array($val)) {
            $this->value = $val['Original'];
        } elseif($val instanceof URLSegment) {
            $this->value = $val->getOriginal();
        } else {
            $this->value = $val;
        }
        return $this;
    }

}
