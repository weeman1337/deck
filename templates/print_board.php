<style>
    .print-container {
        width: 100%;
    }

    .ztable {
        border-collapse: collapse;
        border: 1px solid;
        margin-left: auto;
        margin-right: auto;
        white-space: normal;
        width: 96%;
    }

    .ztable, th {
        border: 2px solid;
        font-weight: bold;
        padding: 5px;
    }

    .ztable, td {
        border: 2px solid;
        padding: 5px;
        vertical-align: top;
    }

    #spalte1 {

    }
    #anderespalten {
        width: 100px;
    }

    .board-title {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 16px;
        text-align: left;
        text-decoration: underline;
    }

    .stack__title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 16px;
    }

    .card {
        margin-bottom: 16px;
        padding-left: 32px;
    }

    .card__title {
        font-size: 16px;
        font-weight: bold;
    }

    .card__body {
        padding-left: 32px;
    }

    .card__labels {
        font-size: 12px;
        margin-bottom: 8px;
    }

    .card__duedate {
        font-size: 12px;
        margin-bottom: 8px;
    }

    .card__description {
        font-size: 12px;
        margin-bottom: 8px;
    }

    .card__attachment {
        font-weight: bold;
        margin-bottom: 8px;
    }

    .card__assignees {
        font-size: 12px;
        margin-bottom: 8px;
    }

    .comments-title {
        font-size: 12px;
        font-weight: bold;
    }

    .comment {
        font-size: 11px;
        margin-bottom: 8px;
        padding-left: 32px;
    }

    .comment:last-of-type {
        margin-bottom: 0;
    }

    .comment__user {
        font-weight: bold;
    }

    .minititel {
        color: #666666;
        font-size: 12px;
        text-align: left;
    }

    @media print {
        #header {
            display: none !important;
        }

        #content {
            padding-top: 0 !important;
        }
    }
</style>

<div class="print-container">
    <img src="<?php p(image_path('deck', 'vrmfr.png')); ?>" width="200" height="70" style="float:right;">
    <h1 class="board-title"><?php echo $title; ?></h1>
    <div class="minititel">erstellt am <?php date_default_timezone_set("Europe/Berlin"); echo date("d.m.Y", time()); ?> um <?php echo date("H:i", time()); ?> Uhr<br><br></div>

<?php
foreach ($stacks as $stack) {
    echo '<hr class="stacks-divider">';
    echo '<h2 class="stack__title">' . $stack['title'] . '</h2>';
    echo '<table class="ztable">';
    echo '<colgroup><col id="spalte1"></colgroup><colgroup id="anderespalten"><col><col><col></colgroup>';
    echo '<thead><th>Beschreibung</th><th>Verantwortlich</th><th>Labels</th><th>Fälligkeit</th></thead>';

    foreach ($stack['cards'] as $card) {
        echo '<tbody><tr><td>';
        echo '<h3 class="card__title">' . $card['title'] . '</h3>';

        if (!empty($card['description'])) {
            echo '<div class="card__description"><b></b> ' . $card['description'] . '</div>';
        }

        if ($card['hasAttachment']) {
            echo '<div class="card__attachment"><img width="32" height="32" src="' . image_path('deck', 'datei.png') . '"></div>';
        }

        if (!empty($card['comments'])) {
            echo '<div class="comments-title">Kommentare</div>';
            echo '<div class="card__comments">';

            foreach ($card['comments'] as $comment) {
                echo '<div class="comment">';
                echo '<div class="comment__creator"><span class="comment__user">' . $comment['creator'] . '</span> ' . $comment['created'] . '</div>';
                echo '<div class="comment__message">';
                echo $comment['message'];
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        }


        echo '</td>';

        echo '<td>';
        if (!empty($card['assignedUsers'])) {
            echo '<div class="card__assignees">';
            echo implode(', ', $card['assignedUsers']);
            echo '</div>';
        }
        echo '</td>';

        echo '<td>';
        if (!empty($card['labels'])) {
            echo '<div class="card__labels">';
            echo implode(', ', $card['labels']);
            echo '</div>';
        }
        echo '</td>';


        echo '<td>';
        if (!empty($card['dueDate'])) {
            echo '<div class="card__duedate">' . $card['dueDate'] . '</div>';
        }
        echo '</td>';



        echo '</tr>';
    }
    echo '</tbody></table>';
}
?>
</div>
