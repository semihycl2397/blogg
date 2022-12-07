<html>

    <body onload='document.forms[0].submit()'>
        <form method='POST' action='{{$serviceUrl}}/{{$responseUrl}}.aspx'>
            <input type='hidden' name='documentUUID' value='{{$transactionUuid}}'>

            </form>
    </body>
<script>

</script>
</html>
