<?php

function hasRow($query)
{
    return $query->num_rows() > 0;
}

function ensureHasRow($query, $message)
{
    if (!hasRow($query)) {
        throw new NotFoundException($message);
    }
}

function getAllProperties($sourceObject, $mode = 'insert')
{
    require_once( APPPATH . 'core/'. 'Annotate.php');

    $destination = array();
    $reflect = new AnnotatedReflectionClass($sourceObject);
    if ($mode == 'insert') {
        foreach ($reflect->getProperties() as $property) {
            if ($property->hasAnnotation('InsertIgnore')) {
                continue;
            } elseif ($property->isPrivate()) {
                continue;
            }
            $name = $property->getName();
            $value = $property->getValue($sourceObject);
            $destination[$name] = $value;
        }
    } elseif ($mode == 'update') {
        foreach ($reflect->getProperties() as $property) {
            if ($property->isPrivate()) {
                continue;
            }
            $name = $property->getName();
            if(array_key_exists($name, get_object_vars($sourceObject))) {
                $value = $property->getValue($sourceObject);
                $destination[$name] = $value;
            }
        }
    }
    return $destination;
}
