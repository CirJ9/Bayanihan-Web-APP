<div class="side-menu">
    <div class="brand-name">
        <h1>BAYANIHAN <span>APP</span></h1>
    </div>
    <ul>
        <li class="<?php echo ($page=='home')?'active':''; ?>">
            <a href="dashboard.php"><span class="material-symbols-outlined">home</span>Home</a>
        </li>
        <li class="<?php echo ($page=='my_tasks')?'active':''; ?>">
            <a href="my_tasks.php"><span class="material-symbols-outlined">assignment_ind</span> My Tasks</a>
        </li>
        <li class="<?php echo ($page=='request')?'active':''; ?>">
            <a href="history.php"><span class="material-symbols-outlined">history_edu</span> My Requests</a>
        </li>
        <li class="<?php echo ($page=='rewards')?'active':''; ?>">
            <a href="rewards.php"><span class="material-symbols-outlined">redeem</span> Rewards</a>
        </li>
        <li class="<?php echo ($page=='stats')?'active':''; ?>">
            <a href="statistics.php"><span class="material-symbols-outlined">analytics</span> Statistics</a>
        </li>
        <li class="<?php echo ($page=='announce')?'active':''; ?>">
            <a href="announcement.php"><span class="material-symbols-outlined">campaign</span> Updates</a>
        </li>
        <li class="<?php echo ($page=='about')?'active':''; ?>">
            <a href="about.php"><span class="material-symbols-outlined">info</span> About Us</a>
        </li>
    </ul>
</div>