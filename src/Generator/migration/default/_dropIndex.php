<?php
/**
 * @var $indexes array
 */
?>

<?php foreach ($indexes as $index): ?>
        // drop index for column `<?= $index['name'] ?>`
        $this->dropIndex(
            '<?= $index['idx']  ?>',
            $this->tableName
        );
<?php endforeach; ?>

