<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit a Presentation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="images/logo.png">

    <?php include "header.php"; ?>

    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #6f42c1;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --light-bg: #f8f9fc;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 20px 0;
            direction: ltr;
            /* Changed to LTR for English */
            text-align: left;
            /* Changed to left for English */
        }

        .container {
            max-width: 900px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-bg);
        }

        .header h2 {
            color: var(--primary-color);
            font-weight: 700;
        }

        .header p {
            color: #858796;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-top: 2rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 10px;
            margin-left: 0;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 0.5rem rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
        }

        .help-text {
            font-size: 0.85em;
            color: #6c757d;
        }

        .message {
            padding: 15px;
            margin-bottom: 1rem;
            border-radius: 5px;
            text-align: center;
            display: none;
            /* Initially hidden */
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

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 25px;
            font-weight: 600;
            border-radius: 30px;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            transition: all 0.3s;
        }

        .required-label:after {
            content: " *";
            color: var(--danger-color);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <h2><i class="fas fa-presentation"></i> Submit a Presentation Request</h2>
            <p class="mt-3">Please fill out the form below to submit your presentation request.</p>
        </div>

        <div class="message success" id="successMessage">
            Your request has been submitted successfully. Thank you!
        </div>

        <div class="message error" id="errorMessage">
            There was a problem submitting your request. Please try again.
        </div>

        <form id="presentationForm" method="post" action="email_request.php" enctype="multipart/form-data">
            <div class="section-title">
                <i class="fas fa-user"></i> Requester Details
            </div>
            <div class="card p-4 mb-4">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label for="requester_title" class="form-label">Title</label>
                        <select class="form-select" name="requester_title" id="requester_title">
                            <option value="">(None)</option>
                            <option value="Mr.">Mr.</option>
                            <option value="Ms.">Ms.</option>
                            <option value="Mrs.">Mrs.</option>
                            <option value="Miss">Miss</option>
                            <option value="Dr.">Dr.</option>
                            <option value="Prof.">Prof.</option>
                            <option value="BS">BS</option>
                            <option value="BA">BA</option>
                            <option value="BSc">BSc</option>
                            <option value="MS">MS</option>
                            <option value="MA">MA</option>
                            <option value="MSc">MSc</option>
                            <option value="MBA">MBA</option>
                            <option value="PhD">PhD</option>
                            <option value="MD">MD</option>
                            <option value="DDS">DDS</option>
                            <option value="DVM">DVM</option>
                            <option value="JD">JD</option>
                            <option value="Psy">Psy</option>
                            <option value="RN">RN</option>
                            <option value="PA">PA</option>
                            <option value="RPh">RPh</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="requester_name" class="form-label required-label">Name</label>
                        <input type="text" class="form-control" name="requester_name" id="requester_name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="requester_email" class="form-label required-label">Email</label>
                        <input type="email" class="form-control" name="requester_email" id="requester_email" required>
                    </div>
                    <div class="col-md-6">
                        <label for="requester_affiliation" class="form-label">Affiliation</label>
                        <input type="text" class="form-control" name="requester_affiliation" id="requester_affiliation">
                    </div>
                    <div class="col-md-6">
                        <label for="requester_phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" name="requester_phone" id="requester_phone">
                    </div>
                </div>
            </div>

            <div class="section-title">
                <i class="fas fa-user-tie"></i> Presenter Details
            </div>
            <div class="card p-4 mb-4">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label for="presenter_title" class="form-label">Title</label>
                        <select class="form-select" name="presenter_title" id="presenter_title">
                            <option value="">(None)</option>
                            <option value="Mr.">Mr.</option>
                            <option value="Ms.">Ms.</option>
                            <option value="Mrs.">Mrs.</option>
                            <option value="Miss">Miss</option>
                            <option value="Dr.">Dr.</option>
                            <option value="Prof.">Prof.</option>
                            <option value="BS">BS</option>
                            <option value="BA">BA</option>
                            <option value="BSc">BSc</option>
                            <option value="MS">MS</option>
                            <option value="MA">MA</option>
                            <option value="MSc">MSc</option>
                            <option value="MBA">MBA</option>
                            <option value="PhD">PhD</option>
                            <option value="MD">MD</option>
                            <option value="DDS">DDS</option>
                            <option value="DVM">DVM</option>
                            <option value="JD">JD</option>
                            <option value="Psy">Psy</option>
                            <option value="RN">RN</option>
                            <option value="PA">PA</option>
                            <option value="RPh">RPh</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="presenter_name" class="form-label required-label">Name</label>
                        <input type="text" class="form-control" name="presenter_name" id="presenter_name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="presenter_email" class="form-label required-label">Email</label>
                        <input type="email" class="form-control" name="presenter_email" id="presenter_email" required>
                    </div>
                    <div class="col-12">
                        <label for="presenter_affiliation" class="form-label">Affiliation</label>
                        <input type="text" class="form-control" name="presenter_affiliation" id="presenter_affiliation">
                    </div>
                </div>
            </div>

            <div class="section-title">
                <i class="fas fa-file-alt"></i> Paper Details
            </div>
            <div class="card p-4 mb-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="paper_title" class="form-label required-label">Paper Title</label>
                        <input type="text" class="form-control" name="paper_title" id="paper_title" required>
                    </div>
                    <div class="col-12">
                        <label for="paper_link" class="form-label">Paper Link</label>
                        <input type="url" class="form-control" name="paper_link" id="paper_link">
                        <div class="form-text help-text">Link to paper (e.g., arXiv, Google Scholar, etc.).</div>
                    </div>

                    <div class="col-md-6">
                        <label for="tags" class="form-label">Keywords</label>
                        <input type="text" class="form-control" name="tags" id="tags">
                        <div class="form-text help-text">Comma-separated keywords (e.g., AI, Machine Learning)</div>
                    </div>


                    <div class="col-12">
                        <label for="paper_file" class="form-label">Paper PDF (optional)</label>
                        <input class="form-control" type="file" name="paper_file" id="paper_file">
                    </div>
                </div>
            </div>

            <div class="section-title">
                <i class="fas fa-trophy"></i> Competition Details
            </div>
            <div class="card p-4 mb-4">
                <div class="mb-3">
                    <label class="form-label">Are you requesting for a competition?</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="include_comp" id="include_comp_yes" value="1">
                        <label class="form-check-label" for="include_comp_yes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="include_comp" id="include_comp_no" value="0" checked>
                        <label class="form-check-label" for="include_comp_no">No</label>
                    </div>
                </div>
                <div id="comp-fields" class="row g-3 d-none">
                    <div class="col-md-6">
                        <label for="comp_name" class="form-label">Competition Name</label>
                        <input type="text" class="form-control" name="comp_name" id="comp_name">
                    </div>
                    <div class="col-md-6">
                        <label for="comp_link" class="form-label">Competition Link</label>
                        <input type="url" class="form-control" name="comp_link" id="comp_link">
                    </div>
                    <div class="col-12">
                        <label for="comp_file" class="form-label">Competition PDF (optional)</label>
                        <input class="form-control" type="file" name="comp_file" id="comp_file">
                    </div>
                    <div class="col-12">
                        <label for="comp_message" class="form-label">Message for competition organizer</label>
                        <textarea class="form-control" name="comp_message" id="comp_message" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <div class="section-title">
                <i class="fas fa-info-circle"></i> Other Details
            </div>
            <div class="card p-4 mb-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="custom_message" class="form-label">Custom message for organizers</label>
                        <textarea class="form-control" name="custom_message" id="custom_message" rows="4"></textarea>
                    </div>
                    <div class="col-12">
                        <label for="cc_emails" class="form-label">CC email addresses</label>
                        <input type="text" class="form-control" name="cc_emails" id="cc_emails">
                        <div class="form-text help-text">Separate multiple emails with commas.</div>
                    </div>
                </div>
            </div>

            <div class="section-title">
                <i class="fas fa-shield-alt"></i> Consent
            </div>
            <div class="card p-4 mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="consent" value="1" id="consent" required>
                    <label class="form-check-label" for="consent">
                        I consent to storing this information for the purposes of organizing the presentation.
                    </label>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane"></i> Send Request
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle competition fields on Yes/No
        document.querySelectorAll('input[name="include_comp"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('comp-fields').classList.toggle('d-none', this.value !== '1');
            });
        });

        // Show message function - triggered by URL parameters
        function showMessage(text, type) {
            const messageEl = document.getElementById(type + 'Message');
            if (messageEl) {
                messageEl.textContent = text;
                messageEl.style.display = 'block';

                setTimeout(() => {
                    messageEl.style.display = 'none';
                }, 5000);
            }
        }

        // Check if there's a message in URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'success') {
            showMessage('Your request has been submitted successfully. Thank you!', 'success');
        } else if (urlParams.get('status') === 'error') {
            showMessage('There was a problem submitting your request. Please try again.', 'error');
        }
    </script>

    <?php include "footer.php" ?>
</body>

</html>