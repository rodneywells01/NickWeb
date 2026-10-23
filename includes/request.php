<?php
// Existing forms use flat strings only. Reject shapes that cause PHP 8 TypeErrors.
function request_has_valid_shape($query, $form) {
    foreach (array($query, $form) as $input) {
        foreach ($input as $value) {
            if (!is_string($value)) {
                return false;
            }
        }
    }

    // These query fields are used as numbers in the legacy event/media handlers.
    foreach (array('moveid', 'deleteid', 'delete', 'event') as $field) {
        if (isset($query[$field]) &&
            !preg_match('/^[1-9][0-9]{0,8}$/D', $query[$field])) {
            return false;
        }
    }
    if (isset($query['movedirection']) &&
        (!isset($query['moveid']) ||
         !in_array($query['movedirection'], array('-1', '1'), true))) {
        return false;
    }

    return true;
}
