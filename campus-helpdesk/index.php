<?php
// index.php
// Main page: shows the support request form.
// When the form is submitted, the data is checked and then saved to the database.

require 'db_connect.php';

// Choices for the drop-down menus and radio buttons.
// These lists are also used to check that the submitted values are valid.
$userTypes  = ['Student', 'Faculty', 'Staff', 'Visitor'];
$categories = ['Account / Password', 'Wi-Fi / Internet', 'Email', 'Computer / Hardware', 'Software', 'Printing', 'Other'];
$priorities = ['Low', 'Medium', 'High'];

// Gets a value from the submitted form and removes extra spaces
function getPost($field)
{
    if (isset($_POST[$field]) && is_string($_POST[$field])) {
        return trim($_POST[$field]);
    }
    return '';
}

// Prints the error message for a field, if it has one
function showError($errors, $field)
{
    if (isset($errors[$field])) {
        echo '<span class="error-text">' . $errors[$field] . '</span>';
    }
}

// Empty values for the first time the page loads
$fullName = $email = $phone = $userType = $category = $subject = $description = '';
$priority = 'Medium';
$errors = [];

// The form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Get the form data
    $fullName    = getPost('full_name');
    $email       = getPost('email');
    $phone       = getPost('phone');
    $userType    = getPost('user_type');
    $category    = getPost('category');
    $priority    = getPost('priority');
    $subject     = getPost('subject');
    $description = getPost('description');

    // 2. Validate the data (the browser checks some of this too, but it can be bypassed)
    if ($fullName === '') {
        $errors['full_name'] = 'Please enter your name.';
    } elseif (strlen($fullName) > 100) {
        $errors['full_name'] = 'Name must be 100 characters or less.';
    }

    if ($email === '') {
        $errors['email'] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    // Phone is optional, but if it's filled in it has to look like a phone number
    if ($phone !== '' && !preg_match('/^[0-9+()\- ]{7,20}$/', $phone)) {
        $errors['phone'] = 'Please enter a valid phone number.';
    }

    // These values must be one of the options from the lists above
    if (!in_array($userType, $userTypes, true)) {
        $errors['user_type'] = 'Please select who you are.';
    }
    if (!in_array($category, $categories, true)) {
        $errors['category'] = 'Please select a category.';
    }
    if (!in_array($priority, $priorities, true)) {
        $errors['priority'] = 'Please select a priority.';
    }

    if ($subject === '') {
        $errors['subject'] = 'Please enter a subject.';
    } elseif (strlen($subject) > 150) {
        $errors['subject'] = 'Subject must be 150 characters or less.';
    }

    if (strlen($description) < 10) {
        $errors['description'] = 'Please describe the problem (at least 10 characters).';
    } elseif (strlen($description) > 2000) {
        $errors['description'] = 'Description must be 2000 characters or less.';
    }

    // 3. No errors, so save the request with a prepared statement
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO support_requests
                        (full_name, email, phone, user_type, category, priority, subject, description)
                    VALUES
                        (:full_name, :email, :phone, :user_type, :category, :priority, :subject, :description)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':full_name'   => $fullName,
                ':email'       => $email,
                ':phone'       => $phone === '' ? null : $phone,
                ':user_type'   => $userType,
                ':category'    => $category,
                ':priority'    => $priority,
                ':subject'     => $subject,
                ':description' => $description,
            ]);

            // The ID that MySQL gave the new row becomes the request number
            $newId = $pdo->lastInsertId();

            // Redirect so that refreshing the page doesn't submit the form twice
            header('Location: index.php?success=' . $newId);
            exit;
        } catch (PDOException $e) {
            error_log('Could not save request: ' . $e->getMessage());
            $errors['database'] = 'Sorry, your request could not be saved. Please try again.';
        }
    }
}

// Request number to show after a successful submission
$successId = isset($_GET['success']) ? (int) $_GET['success'] : 0;

$pageTitle = 'Submit a Request';
$currentPage = 'index';
include 'includes/header.php';
?>

<?php if ($successId > 0): ?>
    <div class="alert success">
        <strong>Thank you!</strong> Your request was submitted.
        Your request number is <strong>HD-<?php echo str_pad($successId, 4, '0', STR_PAD_LEFT); ?></strong>.
        The IT team will contact you soon.
    </div>
<?php endif; ?>

<?php if (isset($errors['database'])): ?>
    <div class="alert error"><?php echo $errors['database']; ?></div>
<?php elseif (!empty($errors)): ?>
    <div class="alert error">Please fix the errors below and submit the form again.</div>
<?php endif; ?>

<div class="card">
    <h1>Submit a Support Request</h1>
    <p class="intro">
        Having trouble with your account, Wi-Fi, a computer or a printer?
        Fill out this form and the IT team will get back to you.
    </p>

    <form action="index.php" method="post">

        <div class="row">
            <div class="field">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" maxlength="100" required
                       value="<?php echo htmlspecialchars($fullName); ?>">
                <?php showError($errors, 'full_name'); ?>
            </div>

            <div class="field">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" maxlength="100" required
                       value="<?php echo htmlspecialchars($email); ?>">
                <?php showError($errors, 'email'); ?>
            </div>
        </div>

        <div class="row">
            <div class="field">
                <label for="phone">Phone <span class="optional">(optional)</span></label>
                <input type="tel" id="phone" name="phone" maxlength="20"
                       value="<?php echo htmlspecialchars($phone); ?>">
                <?php showError($errors, 'phone'); ?>
            </div>

            <div class="field">
                <label for="user_type">I am a *</label>
                <select id="user_type" name="user_type" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($userTypes as $option): ?>
                        <option value="<?php echo $option; ?>" <?php if ($userType === $option) echo 'selected'; ?>>
                            <?php echo $option; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php showError($errors, 'user_type'); ?>
            </div>
        </div>

        <div class="row">
            <div class="field">
                <label for="category">Category *</label>
                <select id="category" name="category" required>
                    <option value="">-- Select a category --</option>
                    <?php foreach ($categories as $option): ?>
                        <option value="<?php echo $option; ?>" <?php if ($category === $option) echo 'selected'; ?>>
                            <?php echo $option; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php showError($errors, 'category'); ?>
            </div>

            <div class="field">
                <span class="label">Priority *</span>
                <div class="radio-group">
                    <?php foreach ($priorities as $option): ?>
                        <label>
                            <input type="radio" name="priority" value="<?php echo $option; ?>"
                                <?php if ($priority === $option) echo 'checked'; ?>>
                            <?php echo $option; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p class="hint">High = you can't work or attend class because of this problem.</p>
                <?php showError($errors, 'priority'); ?>
            </div>
        </div>

        <div class="field">
            <label for="subject">Subject *</label>
            <input type="text" id="subject" name="subject" maxlength="150" required
                   placeholder="Example: Can't connect to Wi-Fi in the library"
                   value="<?php echo htmlspecialchars($subject); ?>">
            <?php showError($errors, 'subject'); ?>
        </div>

        <div class="field">
            <label for="description">Describe the problem *</label>
            <textarea id="description" name="description" maxlength="2000" required
                      placeholder="What happened? Did you see an error message? What have you already tried?"><?php echo htmlspecialchars($description); ?></textarea>
            <?php showError($errors, 'description'); ?>
        </div>

        <button type="submit">Submit Request</button>
        <p class="hint">* Required field</p>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
