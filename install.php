<?php
require_once 'lib/common.php';
require_once 'lib/install.php';

// We store stuff in the session, to survive the redirect to self
session_start();

// Only run the installer when we're responding to the form
if ($_POST){
    // Here's the install
    $pdo = getPDO();
    list($_SESSION['count'], $_SESSION['error']) = installBlog();
	
    // ... and here we redirect from POST to GET
    redirectAndExit('install.php');
}

// Let's see if we've just installed
$attempted = false;
if ($_SESSION){
    $attempted = true;
    $count = $_SESSION['count'];
    $error = $_SESSION['error'];
    // Unset session variables, so we only report the install/failure once
    unset($_SESSION['count']);
    unset($_SESSION['error']);
}
?>
        </style>
    </head>
    <body>
        <?php if ($attempted): ?>
            <?php if ($error): ?>
                <div class="error box">
                    <?php echo $error ?>
                </div>
            <?php else: ?>
                <div class="success box">
                    The database and demo data was created OK.
                    <?php foreach (array('post', 'comment') as $tableName): ?>
                        <?php if (isset($count[$tableName])): ?>
                            <?php // Prints the count ?>
                            <?php echo $count[$tableName] ?> new
                            <?php // Prints the name of the thing ?>
                            <?php echo $tableName ?>s
                            were created.
                        <?php endif ?>
                    <?php endforeach ?>
                </div>
				<p>
					<a href="index.php">Lihat blog</a>
					atau <a href="install.php">pasang ulang</a>
				</p>
            <?php endif ?>
        <?php else: ?>
            <p>Click the install button to reset the database.</p>
            <form method="post">
                <input name="install" type="submit" value="Install"/>
            </form>
        <?php endif ?>
    </body>
</html>
