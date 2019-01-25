<?php
/**
 * @var $foreignKeys array
 */
?>
<?php foreach ($foreignKeys as $foreignKey): ?>
        // drop foreign key for table `<?= $foreignKey['refTable'] ?>`
        $this->dropForeignKey(
            '<?=$foreignKey['fk']?>',
            $this->tableName
        );
<?php endforeach; ?>