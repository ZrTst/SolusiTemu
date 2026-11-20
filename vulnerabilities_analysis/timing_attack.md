# Vulnerability Analysis Report: Timing Attack in User Login Interface

## 1. Vulnerability Overview
Timing attack is a side-channel attack based on time to execute a cryptographic algorithm or a security verification process on a computer.

*   **Vulnerability Principle:** By using automated scripts to make high-frequency requests and measuring the responses time of the backend, an attacker can enumerate the existance of username.
*   **Vulnerability Impact Scope:** Any custom-built authentication mechanism that doesn't return results in constant time.

## 2. Vulnerable code and reproduction
### 2.1 vulnerable code(Before optimized)
In the initial code's logic, it employed early interception by intercepting users that doesn't exist in database.

 ```php
 function db_verifyPassword($user, $password)
{
    if (!$user) {
        return false;
    }
    // Check possible password column names
    $stored_password = $user['Pswd_P'] ?? $user['Pswd_S'] ?? null;
    if ($stored_password === null) {
        return false;
    }
    if (password_verify($password, $stored_password)) {
        return true;
    }
    return false;
}
 ```

### 2.2 Vulnability reproduction
The Bcrypt algorithm is intentionally designed to be CPU-intensive as a defense against brute-force attacks. A single calculation typically takes between **50ms and 100ms**.

When an attacker submits different usernames, a noticeable difference in the server's response time:
1. **Submitting a non-existent username**: The `if (!$user)` condition is triggered, and the program returns `false` immediately within **0.5ms**.
2. **Submitting an existing username (with an incorrect password)**: The initial check is bypassed, and the execution proceeds to `password_verify()`. The server is then forced to do Bcrypt calculation which taking **50ms** before returning `false`.

**Result**: An attacker can enumerate username in the database by only observing the responses time of the server.

## 3. Optimized code and defense principles
This function eliminated timing diferences by forcing the server to conduct a Bcrypt calculation regardless whether the user exits in database.

```php
function db_verifyPassword($user, $password)
{
    // Defined a syntactically valid but meaningless hashed string (Fixed length 60)
    $dummy_hash = '$2y$10$abcdefghijklmnopqrstuvwxyzaaaaaaaaaaaaaaaaaaaaa'; 
    // If the password column in $user array doesn't exists, use forged Bcrypt hash
    $stored_password = $user['Pswd_P'] ?? $user['Pswd_S'] ?? $dummy_hash;
    // Constant timing to verify the password
    $is_match = password_verify($password, $stored_password);
    // Verify validation of user data
    if (empty($user) || (!isset($user['Pswd_P']) && !isset($user['Pswd_S']))) {
        return false;
    }
    return $is_match; 
}
```