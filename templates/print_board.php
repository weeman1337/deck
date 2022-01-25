<style>
    .print-container {
        width: 100%;
    }

    .board-title {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 16px;
    }

    .stack__title {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 16px;
    }

    .card {
        margin-bottom: 16px;
        padding-left: 32px;
    }

    .card__title {
        font-size: 20px;
        font-weight: bold;
    }

    .card__body {
        padding-left: 32px;
    }

    .card__labels {
        margin-bottom: 8px;
    }

    .card__duedate {
        margin-bottom: 8px;
    }

    .card__description {
        margin-bottom: 8px;
    }

    .card__attachment {
        font-weight: bold;
        margin-bottom: 8px;
    }

    .card__assignees {
        margin-bottom: 8px;
    }

    .comments-title {
        font-size: 16px;
        font-weight: bold;
    }

    .comment {
        margin-bottom: 8px;
        padding-left: 32px;
    }

    .comment:last-of-type {
        margin-bottom: 0;
    }

    .comment__user {
        font-weight: bold;
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
    <h1 class="board-title"><?php echo $title; ?></h1>

    <?php
        foreach ($stacks as $stack) {
            echo '<hr class="stacks-divider">';
            echo '<div class="stack">';
            echo '<h2 class="stack__title">' . $stack['title'] . '</h2>';

            echo '<div class="cards">';
            foreach ($stack['cards'] as $card) {
                echo '<div class="card">';
                echo '<h3 class="card__title">' . $card['title'] . '</h3>';
                echo '<div class="card__body">';

                if (!empty($card['assignedUsers'])) {
                    echo '<div class="card__assignees">';
                    echo '<b>Zuständig:</b> ';
                    echo implode(', ', $card['assignedUsers']);
                    echo '</div>';
                }

                if (!empty($card['dueDate'])) {
                    echo '<div class="card__duedate"><b>Fälligkeitsdatum:</b> ' . $card['dueDate'] . '</div>';
                }

                if (!empty($card['labels'])) {
                    echo '<div class="card__labels">';
                    echo '<b>Labels:</b> ';
                    echo implode(', ', $card['labels']);
                    echo '</div>';
                }

                if (!empty($card['description'])) {
                    echo '<div class="card__description"><b>Beschreibung:</b> ' . $card['description'] . '</div>';
                }

                if ($card['hasAttachment']) {
                    echo '<div class="card__attachment">Hat Anhänge</div>';
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

                echo '</div>';
                echo '</div>';
            }
            echo '</div>';

            echo '</div>';
        }
    ?>
</div>
