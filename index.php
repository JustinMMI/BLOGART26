<?php 
require_once 'header.php';
sql_connect();

cookie_notice();
?>



<?php require_once 'footer.php'; ?>

<script>
function onSubmit(token) {
document.getElementById("recaptcha").submit();
console.log(document.getElementById("recaptcha"));
}
</script>