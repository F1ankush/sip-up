<?php
/**
 * Database Error Handler
 * Prevents "too many requests" errors by properly handling database failures
 */

class DatabaseErrorHandler {
    
    /**
     * Handle database errors gracefully
     * @param string $context - Where the error occurred
     * @param string $error - Error message
     * @param string $query - SQL query that failed (optional)
     */
    public static function handleError($context, $error, $query = '') {
        // Log the error
        error_log("[{$context}] Error: {$error}");
        if ($query) {
            error_log("[{$context}] Query: {$query}");
        }
        
        // Check if this is a connection error
        if (strpos($error, 'Connection') !== false || strpos($error, 'connect') !== false) {
            self::showConnectionError();
        } else {
            self::showDatabaseError($context);
        }
    }
    
    /**
     * Show connection error
     */
    private static function showConnectionError() {
        header('HTTP/1.1 503 Service Unavailable');
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Database Connection Error</title>
            <style>
                body { 
                    font-family: 'Segoe UI', Arial, sans-serif; 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    margin: 0;
                    padding: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                }
                .error-container { 
                    background: white; 
                    max-width: 500px;
                    padding: 40px; 
                    border-radius: 10px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                    text-align: center;
                }
                h1 { 
                    color: #d32f2f; 
                    margin: 0 0 10px 0;
                    font-size: 2rem;
                }
                p { 
                    color: #555; 
                    line-height: 1.6;
                    margin: 10px 0;
                    font-size: 1rem;
                }
                .status { 
                    font-size: 3rem; 
                    margin: 20px 0;
                }
                .actions {
                    margin: 30px 0;
                }
                a, button {
                    display: inline-block;
                    margin: 5px;
                    padding: 10px 20px;
                    background: #667eea;
                    color: white;
                    text-decoration: none;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 1rem;
                }
                a:hover, button:hover {
                    background: #764ba2;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="status">⚠️</div>
                <h1>Database Connection Issue</h1>
                <p>We're having trouble connecting to our database. This is a temporary issue and we're working to fix it.</p>
                <p><strong>Status:</strong> Service Temporarily Unavailable (Error 503)</p>
                <div class="actions">
                    <button onclick="location.reload()">Refresh Page</button>
                    <a href="/">Go to Home</a>
                </div>
                <p style="font-size: 0.9rem; color: #999; margin-top: 30px;">
                    If the problem persists, please try again in a few moments or contact support.
                </p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
    
    /**
     * Show database error
     */
    private static function showDatabaseError($context) {
        header('HTTP/1.1 500 Internal Server Error');
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Database Error</title>
            <style>
                body { 
                    font-family: 'Segoe UI', Arial, sans-serif; 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    margin: 0;
                    padding: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                }
                .error-container { 
                    background: white; 
                    max-width: 500px;
                    padding: 40px; 
                    border-radius: 10px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                    text-align: center;
                }
                h1 { 
                    color: #d32f2f; 
                    margin: 0 0 10px 0;
                    font-size: 2rem;
                }
                p { 
                    color: #555; 
                    line-height: 1.6;
                    margin: 10px 0;
                    font-size: 1rem;
                }
                .status { 
                    font-size: 3rem; 
                    margin: 20px 0;
                }
                .actions {
                    margin: 30px 0;
                }
                a, button {
                    display: inline-block;
                    margin: 5px;
                    padding: 10px 20px;
                    background: #667eea;
                    color: white;
                    text-decoration: none;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 1rem;
                }
                a:hover, button:hover {
                    background: #764ba2;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="status">❌</div>
                <h1>Database Error</h1>
                <p>An error occurred while processing your request. Our team has been notified.</p>
                <p><strong>Context:</strong> <?php echo htmlspecialchars($context); ?></p>
                <div class="actions">
                    <button onclick="location.reload()">Retry</button>
                    <a href="/">Go to Home</a>
                </div>
                <p style="font-size: 0.9rem; color: #999; margin-top: 30px;">
                    Error reference has been logged. If you continue experiencing issues, please contact support.
                </p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
?>