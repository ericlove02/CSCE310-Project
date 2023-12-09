<?php
/**
 * Main Author: Mateo Ruiz
 * Co-Author: Eric Love
 */
function makeToast($message, $success)
{
    if (!$success) {
        echo '<script>
            toastr.options = {
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "positionClass": "toast-top-center"
            }
            $(document).ready(function() {
                toastr.error("' . $message . '");
            });
            </script>';
        return;
    }

    echo '<script>
        toastr.options = {
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "positionClass": "toast-top-center"
        }
        $(document).ready(function() {
            toastr.success("' . $message . '");
        });
        </script>';
    return;
}

echo '
<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css" rel="stylesheet"/>
<style>
.toast-success {
    background-color: green !important;
}

.toast-error {
    background-color: red !important;
}
</style>
';
?>