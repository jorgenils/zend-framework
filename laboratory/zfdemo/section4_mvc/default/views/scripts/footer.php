<!-- BEGIN: default/views/scripts/footer.php -->
    <hr>
    <p>
        This is part of the Zend Framework Demo Tutorial:
        <a href="http://framework.zend.com/wiki/display/ZFDEV/Tutorial"
        >http://framework.zend.com/wiki/display/ZFDEV/Tutorial</a>
    </p>

    <p>
        <a href="<?php echo $this->baseUrl ?>">Home</a>
        |
        Tutorial Section: <b><?php echo $this->sectionName; ?></b> |
        Module: <b><?php echo $this->module ?></b> / 
        Controller: <b><?php echo $this->controller ?></b> / 
        Action: <b><?php echo $this->action ?></b>
    </p>

    <p>
        Copyright (c) 2007 Zend Technologies USA Inc.
        (<a href="http://www.zend.com">http://www.zend.com</a>),
        <a href="http://framework.zend.com/license/new-bsd">New BSD License</a>.
    </p>

<?php if ($this->showLog): ?>
    <hr>
    <pre>$_SERVER[] = <?php echo $this->escape(print_r($this->SERVER, true)); ?></pre>
    <hr>
    <h3>Debug Log</h3>
    <pre><?php echo $this->escape($this->log); ?></pre>
<?php endif; ?>

</body>
</html>
<!-- END: default/views/scripts/footer.php -->
