<?php
// Set variables for messages
$message = '';
$message_type = '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit a Presentation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: 900px;
        }

        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #343a40;
            margin-top: 2rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }

        .help-text {
            font-size: 0.9em;
            color: #6c757d;
        }

        .message {
            padding: 15px;
            margin-bottom: 1rem;
            border-radius: 5px;
            text-align: center;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <h2 class="text-center mb-4">Submit a Presentation</h2>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="email_request" enctype="multipart/form-data">

            <div class="section-title">Requester Details</div>
            <div class="card p-4 mb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="requester_name" class="form-label">Name *</label>
                        <input type="text" class="form-control" name="requester_name" id="requester_name" required value="<?php echo htmlspecialchars($_POST['requester_name'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="requester_email" class="form-label">Email *</label>
                        <input type="email" class="form-control" name="requester_email" id="requester_email" required value="<?php echo htmlspecialchars($_POST['requester_email'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="requester_affiliation" class="form-label">Affiliation</label>
                        <input type="text" class="form-control" name="requester_affiliation" id="requester_affiliation" value="<?php echo htmlspecialchars($_POST['requester_affiliation'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="requester_phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" name="requester_phone" id="requester_phone" value="<?php echo htmlspecialchars($_POST['requester_phone'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="section-title">Presenter Details</div>
            <div class="card p-4 mb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="presenter_name" class="form-label">Name *</label>
                        <input type="text" class="form-control" name="presenter_name" id="presenter_name" required value="<?php echo htmlspecialchars($_POST['presenter_name'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="presenter_email" class="form-label">Email *</label>
                        <input type="email" class="form-control" name="presenter_email" id="presenter_email" required value="<?php echo htmlspecialchars($_POST['presenter_email'] ?? ''); ?>">
                    </div>
                    <div class="col-md-12">
                        <label for="presenter_affiliation" class="form-label">Affiliation</label>
                        <input type="text" class="form-control" name="presenter_affiliation" id="presenter_affiliation" value="<?php echo htmlspecialchars($_POST['presenter_affiliation'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="section-title">Paper Details</div>
            <div class="card p-4 mb-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="paper_title" class="form-label">Paper Title *</label>
                        <input type="text" class="form-control" name="paper_title" id="paper_title" required value="<?php echo htmlspecialchars($_POST['paper_title'] ?? ''); ?>">
                    </div>
                    <div class="col-12">
                        <label for="paper_link" class="form-label">Paper Link</label>
                        <input type="url" class="form-control" name="paper_link" id="paper_link" value="<?php echo htmlspecialchars($_POST['paper_link'] ?? ''); ?>">
                        <div class="form-text help-text">Link to paper (e.g. arXiv, Google Scholar, etc.).</div>
                    </div>
                    <div class="col-12">
                        <label for="paper_abstract" class="form-label">Abstract *</label>
                        <textarea class="form-control" name="paper_abstract" id="paper_abstract" rows="10" required><?php echo htmlspecialchars($_POST['paper_abstract'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="tags" class="form-label">Tags</label>
                        <input type="text" class="form-control" name="tags" id="tags" value="<?php echo htmlspecialchars($_POST['tags'] ?? ''); ?>">
                        <div class="form-text help-text">Comma-separated keywords (e.g., AI, Machine Learning)</div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_online" value="1" id="is_online" <?php echo isset($_POST['is_online']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_online">
                                I want to present online.
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label for="paper_file" class="form-label">Paper PDF (optional)</label>
                        <input class="form-control" type="file" name="paper_file" id="paper_file">
                    </div>
                </div>
            </div>

            <div class="section-title">Competition Details</div>
            <div class="card p-4 mb-4">
                <div class="mb-3">
                    <label class="form-label">Are you submitting for a competition?</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="include_comp" id="include_comp_yes" value="1" <?php echo (isset($_POST['include_comp']) && $_POST['include_comp'] == '1') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="include_comp_yes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="include_comp" id="include_comp_no" value="0" <?php echo (!isset($_POST['include_comp']) || $_POST['include_comp'] == '0') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="include_comp_no">No</label>
                    </div>
                </div>
                <div id="comp-fields" class="row g-3 <?php echo (isset($_POST['include_comp']) && $_POST['include_comp'] == '1') ? '' : 'd-none'; ?>">
                    <div class="col-md-6">
                        <label for="comp_name" class="form-label">Competition Name</label>
                        <input type="text" class="form-control" name="comp_name" id="comp_name" value="<?php echo htmlspecialchars($_POST['comp_name'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="comp_link" class="form-label">Competition Link</label>
                        <input type="url" class="form-control" name="comp_link" id="comp_link" value="<?php echo htmlspecialchars($_POST['comp_link'] ?? ''); ?>">
                    </div>
                    <div class="col-12">
                        <label for="comp_file" class="form-label">Competition PDF (optional)</label>
                        <input class="form-control" type="file" name="comp_file" id="comp_file">
                    </div>
                    <div class="col-12">
                        <label for="comp_message" class="form-label">Message for competition organizer</label>
                        <textarea class="form-control" name="comp_message" id="comp_message" rows="5"><?php echo htmlspecialchars($_POST['comp_message'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="section-title">Other Details</div>
            <div class="card p-4 mb-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="custom_message" class="form-label">Custom message for organizers</label>
                        <textarea class="form-control" name="custom_message" id="custom_message" rows="5"><?php echo htmlspecialchars($_POST['custom_message'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-12">
                        <label for="cc_emails" class="form-label">CC email addresses</label>
                        <input type="email" class="form-control" name="cc_emails" id="cc_emails" value="<?php echo htmlspecialchars($_POST['cc_emails'] ?? ''); ?>">
                        <div class="form-text help-text">Separate multiple emails with commas.</div>
                    </div>
                </div>
            </div>

            <div class="section-title">Consent</div>
            <div class="card p-4 mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="consent" value="1" id="consent" required <?php echo isset($_POST['consent']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="consent">
                        I consent to storing this information for the purposes of organizing the presentation.
                    </label>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">Send Request</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        // Toggle competition fields on Yes/No
        (function() {
            var radios = document.querySelectorAll('input[name="include_comp"]');
            var comp = document.getElementById('comp-fields');

            function update() {
                var val = document.querySelector('input[name="include_comp"]:checked');
                if (val && val.value === '1') comp.classList.remove('d-none');
                else comp.classList.add('d-none');
            }
            for (var i = 0; i < radios.length; i++) {
                radios[i].addEventListener('change', update);
            }
            update(); // Initial call
        })();
    </script>
</body>

</html>