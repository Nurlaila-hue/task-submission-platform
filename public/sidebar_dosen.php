<style>
    .sidebar {
        position: sticky;
        top: 18px;
        z-index: 20;
        display: flex;
        flex: 0 0 82px;
        flex-direction: column;
        align-items: center;
        width: 82px;
        height: calc(100vh - 36px);
        min-height: 420px;
        padding: 24px 8px;
        border-radius: 24px;
        background: linear-gradient(180deg, #28285f, #202052);
        box-shadow: 0 18px 45px rgba(35, 35, 91, .2);
    }

    .sidebar .profile-mini {
        width: 54px;
        height: 54px;
        margin-bottom: 10px;
        overflow: hidden;
        border: 3px solid #a6d1ff;
        border-radius: 50%;
        background: #fff;
    }

    .sidebar .profile-mini img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .sidebar .profile-name {
        width: 100%;
        margin-bottom: 22px;
        color: #fff;
        font-size: .76rem;
        font-weight: 700;
        text-align: center;
        overflow-wrap: anywhere;
    }

    .sidebar .menu-link,
    .sidebar .logout-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        margin: 8px 0;
        padding: 8px;
        border: 0;
        border-radius: 15px;
        color: rgba(255,255,255,.85);
        background: transparent;
        text-decoration: none;
        cursor: pointer;
    }

    .sidebar .menu-link:hover,
    .sidebar .menu-link.active,
    .sidebar .logout-link:hover {
        background: #4f8cff;
        color: #fff;
    }

    .sidebar .logout-link {
        width: auto;
        gap: 7px;
        margin-top: auto;
        font-size: .85rem;
    }

    @media (max-width: 700px) {
        .sidebar {
            position: sticky;
            top: 8px;
            flex: 0 0 auto;
            flex-direction: row;
            justify-content: flex-start;
            width: 100%;
            height: auto;
            min-height: 62px;
            padding: 8px 10px;
            border-radius: 18px;
        }

        .sidebar .profile-mini {
            flex: 0 0 42px;
            width: 42px;
            height: 42px;
            margin: 0 8px 0 0;
        }

        .sidebar .profile-name {
            width: auto;
            max-width: 82px;
            margin: 0 auto 0 0;
            font-size: .7rem;
        }

        .sidebar .menu-link {
            flex: 0 0 40px;
            width: 40px;
            height: 40px;
            margin: 0 2px;
        }

        .sidebar .logout-link {
            flex: 0 0 40px;
            width: 40px;
            height: 40px;
            margin: 0 0 0 auto;
        }
    }

    @media (max-width: 430px) {
        .sidebar { padding: 8px; }
        .sidebar .profile-mini {
            flex-basis: 38px;
            width: 38px;
            height: 38px;
            margin-right: 4px;
        }
        .sidebar .profile-name { display: none; }
        .sidebar .menu-link,
        .sidebar .logout-link {
            flex-basis: 38px;
            width: 38px;
            height: 38px;
            margin-left: 1px;
            margin-right: 1px;
            padding: 6px;
        }
        .sidebar .logout-link span { display: none; }
        }
</style>

<aside class="sidebar">
    <div class="profile-mini">
        <img src="<?= htmlspecialchars((string) $profile_pic, ENT_QUOTES, 'UTF-8') ?>"
             alt="Foto profil"
             onerror="this.onerror=null;this.src='assets/default-avatar.png';">
    </div>

    <div class="profile-name">
        <?= htmlspecialchars((string) $lecturerName, ENT_QUOTES, 'UTF-8') ?>
    </div>

    <a class="menu-link<?= ($menu ?? '') === 'profile' ? ' active' : '' ?>"
       href="profile.php" title="Profil" aria-label="Profil">
        <svg width="28" height="28" viewBox="0 0 32 32" fill="none" aria-hidden="true">
            <circle cx="16" cy="12" r="6" stroke="#fff" stroke-width="2"/>
            <path d="M6 26c0-4 4.477-7 10-7s10 3 10 7"
                  stroke="#fff" stroke-width="2" stroke-linecap="round"/>
        </svg>
    </a>

    <a class="menu-link<?= ($menu ?? '') === 'kelas' ? ' active' : '' ?>"
       href="lecturer_dashboard.php?menu=kelas" title="Daftar kelas" aria-label="Daftar kelas">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3" y="4" width="18" height="16" rx="2" stroke="#fff" stroke-width="2"/>
            <path d="M7 4v16" stroke="#fff" stroke-width="2"/>
        </svg>
    </a>

    <a class="menu-link<?= ($menu ?? '') === 'dashboard' ? ' active' : '' ?>"
       href="lecturer_dashboard.php?menu=dashboard" title="Buat kelas" aria-label="Buat kelas">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="9" stroke="#fff" stroke-width="2"/>
            <path d="M12 8v8M8 12h8" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
        </svg>
    </a>

    <a href="logout.php" class="logout-link" title="Logout" aria-label="Logout">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M8 7 3 12l5 5M3 12h12" stroke="#fff" stroke-width="2"/>
            <rect x="15" y="6" width="6" height="12" rx="2" stroke="#fff" stroke-width="2"/>
        </svg>
        <span>Logout</span>
    </a>
</aside>