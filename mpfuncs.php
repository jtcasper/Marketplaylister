<?php

const DATE_FORM = 'mdY';

function findChildWithClass(/** iterable */ $children, string $class)/**: array*/ {
    if ($children instanceof Traversable) {
        $children = iterator_to_array($children);
    }
    return array_filter(
        $children,
        function($child) use ($class) {
            return $child->hasAttributes()
                && $child->attributes->getNamedItem('class')->value == $class;
        }
    );
}
