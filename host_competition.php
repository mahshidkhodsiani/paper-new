<?php

session_start();

if (!isset($_SESSION['user_data'])) {
  header("Location: login.php");
  exit();
}

$userId = $_SESSION['user_data']['id']; // For updating current user's data

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Competition Form — Organizer & Branding</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


  <style>
    .tab-content {
      margin-top: 1rem;
    }

    #logoPreview {
      max-width: 160px;
      max-height: 120px;
      object-fit: contain;
      border: 1px solid #e9ecef;
      padding: 4px;
      border-radius: 6px;
      background: #fff;
    }

    .progress-container {
      margin-bottom: 20px;
    }


    /* استایل‌های شما */
    .search-container {
      position: relative;
    }

    .search-results-box {
      position: absolute;
      width: 100%;
      top: 100%;
      z-index: 1000;
      background-color: white;
      border: 1px solid #ccc;
      border-top: none;
      box-shadow: 0 4px 8px rgba(0, 0, 0, .1);
      max-height: 200px;
      overflow-y: auto;
    }


    @media (min-width: 992px) {
      #sidebar {
        margin-top: 100px;

      }
    }

    .sidebar-card {
      transition: all 0.3s ease;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transform: translateY(0);
      border-radius: 10%;
    }

    .sidebar-card:hover {
      transform: translateY(-10px);
      /* حرکت به بالا هنگام هاور */
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
      /* سایه بیشتر هنگام هاور */
    }
  </style>

  <?php include "config.php"; ?>
</head>

<body class="bg-light">



  <nav>
    <ul class="nav nav-tabs">
      <li class="nav-item">
        <a class="" href="./">
          <img src="images\logo.png" alt="paperet" style="height: 30px;">
        </a>
      </li>
      <li class="nav-item mx-auto d-flex align-items-center" style="width: 50%;">
        <form class="d-flex w-100 search-container" role="search" method="GET" action="search.php">

          <input class="form-control me-2" type="search" name="query" placeholder="Search" aria-label="Search" value="<?php echo htmlspecialchars($search_query ?? ''); ?>">
          <button class="btn btn-info" type="submit">
            <i class="fas fa-search"></i>
          </button>
          <div id="suggestions" class="search-results-box" style="display: none;"></div>
        </form>
        <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
          <li><a href="#" class=" px-2 link-secondary"><i class="fab fa-linkedin"></i> LinkedIn</a></li>
          <li><a href="people" class=" px-2 link-dark"><i class="fas fa-users"></i> People</a></li>
          <li>
            <a href="" class=" px-2 link-dark"><i class="fas fa-flask"></i> Labs</a>
          </li>
        </ul>
      </li>
      <li class="nav-item m-1">
        <a class="btn btn-info" href="login">Sign in</a>
      </li>
      <li class="nav-item m-1">
        <a class="btn btn-info" href="register">Sign up</a>
      </li>
    </ul>
  </nav>






  <div class="container py-4">

    <div class="row">
      <div class="col-md-8">

        <h3 class="mb-3">Competition Form</h3>

        <div class="progress-container">
          <div class="progress" role="progressbar" aria-label="Competition Progress" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="height: 10px;">
            <div class="progress-bar" style="width: 0%"></div>
          </div>
        </div>

        <form id="competitionForm" novalidate action="save_competition.php" method="POST" enctype="multipart/form-data">
          <ul class="nav nav-tabs" id="mainTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="tab-1-tab" data-bs-toggle="tab" data-bs-target="#tab-1" type="button" role="tab" aria-controls="tab-1" aria-selected="true">
                1. Basic info
              </button>
            </li>
            <li class="nav-item"><button class="nav-link" id="tab-2-tab" data-bs-toggle="tab" data-bs-target="#tab-2" type="button" role="tab">2. Details</button></li>
            <li class="nav-item"><button class="nav-link" id="tab-3-tab" data-bs-toggle="tab" data-bs-target="#tab-3" type="button" role="tab">3. Privacy & Voting</button></li>
            <li class="nav-item"><button class="nav-link" id="tab-4-tab" data-bs-toggle="tab" data-bs-target="#tab-4" type="button" role="tab">4. People & Roles</button></li>
            <li class="nav-item"><button class="nav-link" id="tab-5-tab" data-bs-toggle="tab" data-bs-target="#tab-5" type="button" role="tab">5. Submission Requirements</button></li>
            <li class="nav-item"><button class="nav-link" id="tab-6-tab" data-bs-toggle="tab" data-bs-target="#tab-6" type="button" role="tab">6. Awards & Prizes</button></li>
            <li class="nav-item"><button class="nav-link" id="tab-7-tab" data-bs-toggle="tab" data-bs-target="#tab-7" type="button" role="tab">7. Advanced Settings</button></li>
          </ul>

          <div class="tab-content" id="mainTabsContent">
            <div class="tab-pane fade show active" id="tab-1" role="tabpanel" aria-labelledby="tab-1-tab">
              <div class="card shadow-sm p-3">
                <h5 class="card-title">Organizer & Branding</h5>
                <div class="mb-3">
                  <label for="organizer" class="form-label">Organization *</label>
                  <input type="text" class="form-control" id="organizer" name="organizer"
                    placeholder="Enter your organizer name" required>
                  <div class="invalid-feedback">
                    Please enter your organization name. This will be displayed to participants and judges.
                  </div>
                  <div class="form-text">This will be displayed to participants and judges.</div>
                </div>

                <div class="mb-3">
                  <label for="organizerEmail" class="form-label">Organizer Contact Email *</label>
                  <input type="email" class="form-control" id="organizerEmail" name="organizerEmail"
                    placeholder="example@mail.com" required>
                  <div class="invalid-feedback">
                    Please provide a valid email address.
                  </div>
                </div>

                <div class="mb-3">
                  <label for="logo" class="form-label">Logo (optional)</label>
                  <input class="form-control" type="file" id="logo" name="logo" accept="image/*">
                  <div class="form-text">Accepted formats: JPG, PNG — recommended size under 2MB.</div>

                  <div class="mt-2 d-flex align-items-center gap-3">
                    <img id="logoPreview" src="" alt="Logo Preview" style="display:none;">
                    <div id="logoInfo" class="text-muted small">No logo uploaded.</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="tab-2" role="tabpanel" aria-labelledby="tab-2-tab">
              <div class="card shadow-sm p-3">
                <h5 class="card-title">Competition Details</h5>
                <div class="mb-3">
                  <label for="competitionTitle" class="form-label">Competition Title *</label>
                  <input type="text" class="form-control" id="competitionTitle" name="competitionTitle"
                    placeholder="Enter competition title" required>
                </div>
                <div class="mb-3">
                  <label for="competitionDescription" class="form-label">Description *</label>
                  <textarea class="form-control" id="competitionDescription" name="competitionDescription"
                    rows="3" required></textarea>
                </div>
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="startDate" class="form-label">Start Date *</label>
                    <input type="date" class="form-control" id="startDate" name="startDate" required>
                  </div>
                  <div class="col-md-6">
                    <label for="endDate" class="form-label">End Date *</label>
                    <input type="date" class="form-control" id="endDate" name="endDate" required>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="timezone" class="form-label">Timezone</label>
                  <select class="form-select" id="timezone" name="timezone">
                    <option>America/Chicago (CT)</option>
                    <option>America/New_York (ET)</option>
                    <option>America/Los_Angeles (PT)</option>
                    <option>UTC</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="roomLink" class="form-label">Room / Meeting Link</label>
                  <input type="url" class="form-control" id="roomLink" name="roomLink"
                    placeholder="https://example.com/meeting">
                </div>
                <div class="mb-3">
                  <label for="sessionTrack" class="form-label">Session / Track</label>
                  <input type="text" class="form-control" id="sessionTrack" name="sessionTrack"
                    placeholder="Use commas for multiple tracks">
                </div>
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="presentationDuration" class="form-label">Presentation Duration (minutes)</label>
                    <input type="number" class="form-control" id="presentationDuration"
                      name="presentationDuration" value="15" min="1">
                  </div>
                  <div class="col-md-6">
                    <label for="bufferDuration" class="form-label">Buffer Between Presentations (minutes)</label>
                    <input type="number" class="form-control" id="bufferDuration" name="bufferDuration"
                      value="5" min="0">
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Presentation Order</label>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="presentationOrder"
                      id="orderRandom" checked>
                    <label class="form-check-label" for="orderRandom">
                      Randomize
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="presentationOrder"
                      id="orderLock">
                    <label class="form-check-label" for="orderLock">
                      Lock order after publishing
                    </label>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="tab-3" role="tabpanel" aria-labelledby="tab-3-tab">
              <div class="card shadow-sm p-3">
                <h5 class="card-title">Privacy & Voting</h5>
                <div class="mb-3">
                  <label class="form-label">Competition Visibility *</label>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="competitionVisibility"
                      id="visibilityPublic" checked>
                    <label class="form-check-label" for="visibilityPublic">
                      Public - Anyone can view and participate
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="competitionVisibility"
                      id="visibilityPrivate">
                    <label class="form-check-label" for="visibilityPrivate">
                      Private - Only invited participants can join
                    </label>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Participation Access *</label>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="participationAccess"
                      id="accessOpen" checked>
                    <label class="form-check-label" for="accessOpen">
                      Open to all — anyone can join immediately
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="participationAccess"
                      id="accessApplication">
                    <label class="form-check-label" for="accessApplication">
                      Application required — applicants must be accepted by the host
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="participationAccess"
                      id="accessPrivate">
                    <label class="form-check-label" for="accessPrivate">
                      Private / invite-only — only invited users can join
                    </label>
                  </div>
                  <div class="form-text">Tip: choosing Private will also set visibility to Private.</div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Voting System *</label>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="votingSystem" id="votingJudgesOnly"
                      checked>
                    <label class="form-check-label" for="votingJudgesOnly">
                      Judges Only - Only assigned judges can vote
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="votingSystem" id="votingPublic">
                    <label class="form-check-label" for="votingPublic">
                      Public Voting - Anyone can vote
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="votingSystem" id="votingHybrid">
                    <label class="form-check-label" for="votingHybrid">
                      Hybrid - Judges and public voting combined
                    </label>
                  </div>
                </div>

                <div class="mb-3">
                  <label for="maxVotes" class="form-label">Maximum Votes per Participant</label>
                  <input type="number" class="form-control" id="maxVotes" name="maxVotes" min="1" value="1">
                  <div class="form-text">Applies only to public voting</div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Results Visibility</label>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="resultsVisibility" id="resultsAlwaysVisible"
                      checked>
                    <label class="form-check-label" for="resultsAlwaysVisible">
                      Always visible to everyone
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="resultsVisibility"
                      id="resultsAfterVoting">
                    <label class="form-check-label" for="resultsAfterVoting">
                      Visible after voting ends
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="resultsVisibility"
                      id="resultsAfterCompetition">
                    <label class="form-check-label" for="resultsAfterCompetition">
                      Visible after competition closes
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="resultsVisibility"
                      id="resultsOnlyJudges">
                    <label class="form-check-label" for="resultsOnlyJudges">
                      Only visible to judges
                    </label>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="tab-4" role="tabpanel" aria-labelledby="tab-4-tab">
              <div class="card shadow-sm p-3">
                <h5 class="card-title">People & Roles</h5>
                <div class="mb-3">
                  <h6 class="card-subtitle mb-2 text-muted">Judges</h6>
                  <p class="form-text">Add judges who will evaluate submissions</p>
                  <div id="judgesContainer"></div>
                  <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="addJudgeBtn">Add Judge</button>
                  <div class="form-text mt-2">Each judge can have a title and optional resume (PDF/DOC) and LinkedIn profile.</div>
                </div>
                <hr>
                <div class="mb-3">
                  <h6 class="card-subtitle mb-2 text-muted">Participants</h6>
                  <p class="form-text">Add participants (optional for private competitions)</p>
                  <div id="participantsContainer"></div>
                  <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="addParticipantBtn">Add Participant</button>
                </div>
                <div class="mb-3">
                  <label for="maxParticipants" class="form-label">Maximum Participants</label>
                  <input type="number" class="form-control" id="maxParticipants" name="maxParticipants" placeholder="Leave empty for no limit">
                </div>
                <div class="mb-3">
                  <label for="maxSubmissions" class="form-label">Maximum Submissions per Participant</label>
                  <input type="number" class="form-control" id="maxSubmissions" name="maxSubmissions" value="1" min="1">
                </div>
                <div class="mb-3">
                  <label for="judgeNotes" class="form-label">Judge Calibration Notes</label>
                  <textarea class="form-control" id="judgeNotes" name="judgeNotes" rows="3"></textarea>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="tab-5" role="tabpanel" aria-labelledby="tab-5-tab">
              <div class="card shadow-sm p-3">
                <h5 class="card-title">Submission Requirements</h5>
                <div class="mb-3">
                  <label class="form-label">Submission Type *</label>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="submissionType" id="submissionTypeFile" checked>
                    <label class="form-check-label" for="submissionTypeFile">
                      File Upload
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="submissionType" id="submissionTypeUrl">
                    <label class="form-check-label" for="submissionTypeUrl">
                      URL Submission
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="submissionType" id="submissionTypeText">
                    <label class="form-check-label" for="submissionTypeText">
                      Text Entry
                    </label>
                  </div>
                </div>

                <div class="mb-3">
                  <label for="maxFileSize" class="form-label">Maximum File Size (MB)</label>
                  <input type="number" class="form-control" id="maxFileSize" name="maxFileSize" placeholder="e.g., 50">
                  <div class="form-text">Applies to file uploads only</div>
                </div>

                <div class="mb-3">
                  <label for="allowedFormats" class="form-label">Allowed File Formats</label>
                  <select class="form-select" id="allowedFormats" name="allowedFormats[]" multiple size="8">
                    <option>PDF</option>
                    <option>DOC</option>
                    <option>DOCX</option>
                    <option>JPG</option>
                    <option>PNG</option>
                    <option>ZIP</option>
                    <option>MP4</option>
                    <option>MOV</option>
                  </select>
                  <div class="form-text">Hold Ctrl/Cmd to select multiple</div>
                </div>

                <div class="mb-3">
                  <label for="competitionRubric" class="form-label">Competition Rubric (PDF)</label>
                  <input class="form-control" type="file" id="competitionRubric" name="competitionRubric" accept=".pdf">
                  <div class="form-text">Upload a detailed scoring rubric as a PDF (optional).</div>
                </div>

                <div class="mb-3">
                  <label for="submissionGuidelines" class="form-label">Submission Guidelines</label>
                  <textarea class="form-control" id="submissionGuidelines" name="submissionGuidelines" rows="3" placeholder="Enter custom submission guidelines"></textarea>
                </div>

                <div class="mb-3">
                  <label for="customFields" class="form-label">Custom Submission Fields (JSON)</label>
                  <textarea class="form-control" id="customFields" name="customFields" rows="3" placeholder="Add custom fields to the submission form using JSON format"></textarea>
                </div>

                <div class="mb-3">
                  <label for="slideDeck" class="form-label">Slide Deck (required)</label>
                  <input class="form-control" type="file" id="slideDeck" name="slideDeck" accept=".pdf,.ppt,.pptx" required>
                  <div class="form-text">Upload slides as PDF or PowerPoint.</div>
                </div>

                <div class="mb-3">
                  <label for="abstractText" class="form-label">Abstract (text)</label>
                  <textarea class="form-control" id="abstractText" name="abstractText" rows="3"></textarea>
                </div>

                <div class="mb-3">
                  <label for="posterImage" class="form-label">Poster Image (optional)</label>
                  <input class="form-control" type="file" id="posterImage" name="posterImage" accept="image/*">
                </div>

                <div class="mb-3">
                  <label class="form-label">Consents</label>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="consentRecording" name="consentRecording">
                    <label class="form-check-label" for="consentRecording">
                      I consent to recording/photography of my presentation
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="consentPublicDisplay" name="consentPublicDisplay">
                    <label class="form-check-label" for="consentPublicDisplay">
                      I consent to public display of my slides/abstract
                    </label>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="tab-6" role="tabpanel" aria-labelledby="tab-6-tab">
              <div class="card shadow-sm p-3">
                <h5 class="card-title">Awards & Prizes</h5>
                <div id="awardsContainer">
                  <div class="card p-3 mb-2 bg-light award-item">
                    <div class="d-flex justify-content-end mb-2">
                      <button type="button" class="btn-close remove-award-btn" aria-label="Remove Award"></button>
                    </div>
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label for="awardName1" class="form-label">Award Name</label>
                        <input type="text" class="form-control" id="awardName1" name="awardName[]">
                      </div>
                      <div class="col-md-6">
                        <label for="awardValue1" class="form-label">Award Value</label>
                        <input type="text" class="form-control" id="awardValue1" name="awardValue[]">
                      </div>
                      <div class="col-md-6">
                        <label for="numberOfWinners1" class="form-label">Number of Winners</label>
                        <input type="number" class="form-control" id="numberOfWinners1" name="numberOfWinners[]" min="1">
                      </div>
                      <div class="col-md-6">
                        <label for="perWinnerPrize1" class="form-label">Per‑Winner Prize (optional)</label>
                        <input type="text" class="form-control" id="perWinnerPrize1" name="perWinnerPrize[]">
                      </div>
                    </div>
                  </div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="addAwardBtn">Add Another Award</button>

                <hr class="my-4">

                <div class="mb-3">
                  <label class="form-label">Certificate Template</label>
                  <select class="form-select" id="certificateTemplate" name="certificateTemplate">
                    <option selected>Default Template</option>
                    <option>Elegant Template</option>
                    <option>Modern Template</option>
                    <option>Custom Template</option>
                  </select>
                  <div class="form-text">Certificates will be generated for winners, participants, and judges</div>
                </div>

                <div class="mb-3">
                  <label for="sampleCertificate" class="form-label">Sample Certificate (optional)</label>
                  <input class="form-control" type="file" id="sampleCertificate" name="sampleCertificate" accept="image/*,.pdf">
                  <div class="form-text">Upload a sample certificate design to use for winners.</div>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="tab-7" role="tabpanel" aria-labelledby="tab-7-tab">
              <div class="card shadow-sm p-3">
                <h5 class="card-title">Advanced Settings</h5>
                <div class="mb-3">
                  <label for="scoringRubric" class="form-label">Scoring Rubric</label>
                  <textarea class="form-control" id="scoringRubric" name="scoringRubric" rows="4" placeholder="Define your scoring criteria in JSON format"></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">Score Weighting System</label>
                  <select class="form-select" id="scoreWeighting" name="scoreWeighting">
                    <option>No weighting</option>
                    <option>Normalized scoring</option>
                    <option>Weighted criteria</option>
                    <option>Custom formula</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="customCss" class="form-label">Custom CSS</label>
                  <textarea class="form-control" id="customCss" name="customCss" rows="3" placeholder="Add custom CSS to style the competition page"></textarea>
                </div>
                <div class="mb-3">
                  <label for="redirectUrl" class="form-label">Redirect URL after Submission</label>
                  <input type="url" class="form-control" id="redirectUrl" name="redirectUrl" placeholder="https://example.com/thank-you">
                </div>
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="enableComments" name="enableComments">
                  <label class="form-check-label" for="enableComments">
                    Enable comments on submissions
                  </label>
                  <div class="form-text">Let viewers and judges leave feedback; can be moderated.</div>
                </div>
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="moderateSubmissions" name="moderateSubmissions">
                  <label class="form-check-label" for="moderateSubmissions">
                    Moderate submissions before they are public
                  </label>
                  <div class="form-text">Submissions require organizer approval before appearing to others.</div>
                </div>
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="enableBlindReview" name="enableBlindReview">
                  <label class="form-check-label" for="enableBlindReview">
                    Enable blind review (hide participant identities from judges)
                  </label>
                  <div class="form-text">Judge view hides names, emails, and affiliations during scoring.</div>
                </div>
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="requireConflict" name="requireConflict">
                  <label class="form-check-label" for="requireConflict">
                    Require judges to self-report conflicts of interest
                  </label>
                  <div class="form-text">Judges confirm no conflicts before entering scores.</div>
                </div>
                <div class="mb-3">
                  <label for="lateSubmissionGrace" class="form-label">Late Submission Grace Period (minutes)</label>
                  <input type="number" class="form-control" id="lateSubmissionGrace" name="lateSubmissionGrace" min="0">
                  <div class="form-text">Allow a short window for late uploads without penalty.</div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Judging Visibility</label>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="judgingVisibility" id="judgingPrivate" checked>
                    <label class="form-check-label" for="judgingPrivate">
                      Private (only judges & admins)
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="judgingVisibility" id="judgingPublicAfter">
                    <label class="form-check-label" for="judgingPublicAfter">
                      Public after competition closes
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="judgingVisibility" id="judgingPublicDuring">
                    <label class="form-check-label" for="judgingPublicDuring">
                      Public during competition
                    </label>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="webhookUrl" class="form-label">Webhook URL (optional)</label>
                  <input type="url" class="form-control" id="webhookUrl" name="webhookUrl" placeholder="https://example.com/webhook">
                  <div class="form-text">Receive real-time events for new submissions, status changes, and awards.</div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Export Options</label>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="exportCsv" name="exportOptions[]" value="csv">
                    <label class="form-check-label" for="exportCsv">CSV</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="exportJson" name="exportOptions[]" value="json">
                    <label class="form-check-label" for="exportJson">JSON</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="exportPdf" name="exportOptions[]" value="pdf">
                    <label class="form-check-label" for="exportPdf">PDF Summary</label>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Per-criterion Score Scale</label>
                  <select class="form-select" id="scoreScale" name="scoreScale">
                    <option>1–5</option>
                    <option>1–10</option>
                  </select>
                  <div class="form-text">Used when normalizing totals.</div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Tie-break Policy</label>
                  <select class="form-select" id="tieBreakPolicy" name="tieBreakPolicy">
                    <option>Highest score on "Impact"</option>
                    <option>Chair decision</option>
                    <option>Earliest submission</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="qaTime" class="form-label">Q&A Time per Presentation (minutes)</label>
                  <input type="number" class="form-control" id="qaTime" name="qaTime" min="0">
                </div>
                <div class="mb-3">
                  <label class="form-label">Live Leaderboard Visibility</label>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="leaderboardVisibility" id="leaderboardJudges" checked>
                    <label class="form-check-label" for="leaderboardJudges">
                      Judges only
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="leaderboardVisibility" id="leaderboardPublicAfter">
                    <label class="form-check-label" for="leaderboardPublicAfter">
                      Public after competition closes
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="leaderboardVisibility" id="leaderboardPublicDuring">
                    <label class="form-check-label" for="leaderboardPublicDuring">
                      Public during competition
                    </label>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Email Notifications</label>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="notifyNewSubmission" name="notifyNewSubmission">
                    <label class="form-check-label" for="notifyNewSubmission">
                      Notify on new submission
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="sendSchedule" name="sendSchedule">
                    <label class="form-check-label" for="sendSchedule">
                      Send schedule to presenters & judges
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="emailWinners" name="emailWinners">
                    <label class="form-check-label" for="emailWinners">
                      Email winners when results are published
                    </label>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="resultsPublishDate" class="form-label">Results Publish Date (optional)</label>
                  <input type="date" class="form-control" id="resultsPublishDate" name="resultsPublishDate">
                </div>
                <div class="mb-3">
                  <label for="winnerEmailTemplate" class="form-label">Winner Announcement Template (Email)</label>
                  <textarea class="form-control" id="winnerEmailTemplate" name="winnerEmailTemplate" rows="3"></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-4 d-flex justify-content-between align-items-center">
            <button id="prevBtn" class="btn btn-secondary" type="button" style="display:none;">Previous</button>
            <button id="nextBtn" class="btn btn-primary" type="button">Next</button>
            <button id="submitBtn" class="btn btn-success" type="button" style="display:none;">Submit Form</button>
          </div>
        </form>

      </div>

      <div class="col-md-4" id="sidebar">

        <div class="card sidebar-card">

          <div class="card-body">
            <h5 class="card-title">Getting Started</h5>

            <p class="card-text">
              New to Paperet? Follow these steps to set up your first competition:
            </p>
            <ul>
              <li>Fill out basic competition details</li>
              <li>Configure privacy and voting settings</li>
              <li>Invite participants and judges</li>
              <li>Set up submission requirements</li>
              <li>Configure awards and certificates</li>
            </ul>

          </div>
        </div>



        <div class="card mt-5 p-2 sidebar-card">

          <h3 class="text-center">Your Competitions</h3>

          <div class="card-body">
            <?php
            // تعریف کوئری با INNER JOIN برای جوین دو جدول
            $select = "SELECT 
                    c.id, 
                    c.organizer_name, 
                    c.start_date, 
                    c.end_date, 
                    cp.user_id 
                   FROM 
                    competitions c
                   INNER JOIN 
                    competition_participants cp 
                   ON 
                    c.id = cp.competition_id 
                   WHERE 
                    cp.user_id = $userId";

            $result = $conn->query($select);

            if ($result->num_rows > 0) {
            ?>
              <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">name competition</th>
                      <th scope="col">start</th>
                      <th scope="col">end</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $counter = 1;
                    // نمایش اطلاعات هر مسابقه
                    while ($row = $result->fetch_assoc()) { ?>
                      <tr>
                        <th scope="row"><?php echo $counter; ?></th>
                        <td><?php echo htmlspecialchars($row['organizer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                      </tr>
                    <?php
                      $counter++;
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            <?php
            } else {
              echo "<p>You have not participated in any competitions.</p>";
            }
            ?>
          </div>
        </div>



      </div>


    </div>


  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    (function() {
      const form = document.getElementById('competitionForm');
      const nextBtn = document.getElementById('nextBtn');
      const prevBtn = document.getElementById('prevBtn');
      const submitBtn = document.getElementById('submitBtn');
      const mainTabs = document.getElementById('mainTabs');
      const progressBar = document.querySelector('.progress-bar');
      const tabButtons = document.querySelectorAll('.nav-link');
      const totalTabs = tabButtons.length;
      let currentTab = 0;

      function updateProgressBar() {
        const progress = (currentTab + 1) / totalTabs * 100;
        progressBar.style.width = progress + '%';
        progressBar.setAttribute('aria-valuenow', progress);
      }

      function showTab(n) {
        // Deactivate all tab buttons and their associated panes
        tabButtons.forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));

        // Activate the new tab button and its associated pane
        tabButtons[n].classList.add('active');
        const targetPane = document.querySelector(tabButtons[n].dataset.bsTarget);
        if (targetPane) {
          targetPane.classList.add('show', 'active');
        }

        currentTab = n;

        // Update button visibility
        prevBtn.style.display = (currentTab === 0) ? 'none' : 'block';
        nextBtn.style.display = (currentTab === totalTabs - 1) ? 'none' : 'block';
        submitBtn.style.display = (currentTab === totalTabs - 1) ? 'block' : 'none';

        updateProgressBar();
      }

      function validateTab(tabIndex) {
        const tabPane = document.querySelectorAll('.tab-pane')[tabIndex];
        const requiredInputs = tabPane.querySelectorAll('[required]');
        let isValid = true;

        tabPane.classList.remove('was-validated');

        requiredInputs.forEach(input => {
          if (!input.checkValidity()) {
            isValid = false;
          }
        });

        if (!isValid) {
          tabPane.classList.add('was-validated');
        } else {
          tabPane.classList.remove('was-validated');
        }

        return isValid;
      }

      nextBtn.addEventListener('click', function() {
        if (validateTab(currentTab)) {
          showTab(currentTab + 1);
        }
      });

      prevBtn.addEventListener('click', function() {
        showTab(currentTab - 1);
      });

      submitBtn.addEventListener('click', function() {
        if (validateTab(currentTab)) {
          form.submit();
        }
      });

      tabButtons.forEach((btn, index) => {
        btn.addEventListener('click', () => {
          if (validateTab(currentTab)) {
            showTab(index);
          }
        });
      });

      // Initial tab and progress bar setup
      showTab(0);

      const logoInput = document.getElementById('logo');
      const logoPreview = document.getElementById('logoPreview');
      const logoInfo = document.getElementById('logoInfo');
      const addJudgeBtn = document.getElementById('addJudgeBtn');
      const judgesContainer = document.getElementById('judgesContainer');
      const addParticipantBtn = document.getElementById('addParticipantBtn');
      const participantsContainer = document.getElementById('participantsContainer');
      const addAwardBtn = document.getElementById('addAwardBtn');
      const awardsContainer = document.getElementById('awardsContainer');
      let judgeCount = 0;
      let participantCount = 0;
      let awardCount = 1;

      function createJudgeFields() {
        judgeCount++;
        const newJudgeHtml = `
                    <div class="card p-3 mb-2 bg-light judge-item" data-id="${judgeCount}">
                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" class="btn-close remove-item-btn" aria-label="Remove Judge"></button>
                        </div>
                        <div class="mb-3">
                            <label for="judgeName${judgeCount}" class="form-label">Name</label>
                            <input type="text" class="form-control" id="judgeName${judgeCount}" name="judgeName[]" placeholder="Enter judge's name">
                        </div>
                        <div class="mb-3">
                            <label for="judgeEmail${judgeCount}" class="form-label">Email</label>
                            <input type="email" class="form-control" id="judgeEmail${judgeCount}" name="judgeEmail[]" placeholder="Enter judge's email">
                        </div>
                        <div class="mb-3">
                            <label for="judgeTitle${judgeCount}" class="form-label">Title</label>
                            <input type="text" class="form-control" id="judgeTitle${judgeCount}" name="judgeTitle[]" placeholder="e.g., Senior Designer">
                        </div>
                        <div class="mb-3">
                            <label for="judgeLinkedIn${judgeCount}" class="form-label">LinkedIn (optional)</label>
                            <input type="url" class="form-control" id="judgeLinkedIn${judgeCount}" name="judgeLinkedIn[]" placeholder="https://linkedin.com/in/profile">
                        </div>
                        <div class="mb-3">
                            <label for="judgeResume${judgeCount}" class="form-label">Resume (optional)</label>
                            <input type="file" class="form-control" id="judgeResume${judgeCount}" name="judgeResume[]" accept=".pdf,.doc,.docx">
                        </div>
                    </div>
                `;
        judgesContainer.insertAdjacentHTML('beforeend', newJudgeHtml);
      }

      function createParticipantFields() {
        participantCount++;
        const newParticipantHtml = `
                    <div class="card p-3 mb-2 bg-light participant-item" data-id="${participantCount}">
                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" class="btn-close remove-item-btn" aria-label="Remove Participant"></button>
                        </div>
                        <div class="mb-3">
                            <label for="participantName${participantCount}" class="form-label">Name</label>
                            <input type="text" class="form-control" id="participantName${participantCount}" name="participantName[]" placeholder="Enter participant's name">
                        </div>
                        <div class="mb-3">
                            <label for="participantEmail${participantCount}" class="form-label">Email</label>
                            <input type="email" class="form-control" id="participantEmail${participantCount}" name="participantEmail[]" placeholder="Enter participant's email">
                        </div>
                    </div>
                `;
        participantsContainer.insertAdjacentHTML('beforeend', newParticipantHtml);
      }

      function createAwardFields() {
        awardCount++;
        const newAwardHtml = `
                    <div class="card p-3 mb-2 bg-light award-item" data-id="${awardCount}">
                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" class="btn-close remove-award-btn" aria-label="Remove Award"></button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="awardName${awardCount}" class="form-label">Award Name</label>
                                <input type="text" class="form-control" id="awardName${awardCount}" name="awardName[]">
                            </div>
                            <div class="col-md-6">
                                <label for="awardValue${awardCount}" class="form-label">Award Value</label>
                                <input type="text" class="form-control" id="awardValue${awardCount}" name="awardValue[]">
                            </div>
                            <div class="col-md-6">
                                <label for="numberOfWinners${awardCount}" class="form-label">Number of Winners</label>
                                <input type="number" class="form-control" id="numberOfWinners${awardCount}" name="numberOfWinners[]" min="1">
                            </div>
                            <div class="col-md-6">
                                <label for="perWinnerPrize${awardCount}" class="form-label">Per‑Winner Prize (optional)</label>
                                <input type="text" class="form-control" id="perWinnerPrize${awardCount}" name="perWinnerPrize[]">
                            </div>
                        </div>
                    </div>
                `;
        awardsContainer.insertAdjacentHTML('beforeend', newAwardHtml);
      }

      // Event Listeners
      addJudgeBtn.addEventListener('click', createJudgeFields);
      addParticipantBtn.addEventListener('click', createParticipantFields);
      addAwardBtn.addEventListener('click', createAwardFields);

      // Universal event listener for removing items
      document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item-btn') || e.target.classList.contains('remove-award-btn')) {
          e.target.closest('.card').remove();
        }
      });

      logoInput.addEventListener('change', function() {
        const file = this.files && this.files[0];
        if (!file) {
          logoPreview.style.display = 'none';
          logoInfo.textContent = 'No logo uploaded.';
          return;
        }
        const url = URL.createObjectURL(file);
        logoPreview.src = url;
        logoPreview.style.display = '';
        logoInfo.textContent = file.name + ' — ' + Math.round(file.size / 1024) + ' KB';
      });

    })();
  </script>

  <?php include 'footer.php'; ?>


</body>

</html>