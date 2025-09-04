<style>
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
</style>
<nav>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link" href="./">
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
                <li><a href="#" class="nav-link px-2 link-secondary"><i class="fab fa-linkedin"></i> LinkedIn</a></li>
                <li><a href="people" class="nav-link px-2 link-dark"><i class="fas fa-users"></i> People</a></li>
                <li>
                    <a href="" class="nav-link px-2 link-dark"><i class="fas fa-flask"></i> Labs</a>
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
