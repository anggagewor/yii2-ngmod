<?php
/**
 * @var $indexes array
 */
?>

<?php foreach ($indexes as $index): ?>
        // creates index for column `<?= $index['name'] ?>`
        $this->createIndex(
            '<?= $index['idx']  ?>',
            $this->tableName,
            '<?= $index['name'] ?>'
        );
<?php endforeach; ?>

