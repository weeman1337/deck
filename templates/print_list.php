<style>
    .content {
        padding: 32px;
    }

    .h1 {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 16px;
    }
</style>

<div class="content">
<h1 class="h1">Boards</h1>
<ul>

<?php

foreach ($boards as $board) {
    echo '<li><a href="?board=' . $board['id'] . '">' . $board['title'] . '</a></li>';
}

?>

</ul>
</div>
