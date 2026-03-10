    <div class="page-wrapper">
        <div class="page-content--bge5">
            <div class="container">
                <div class="login-wrap">
                    <div class="login-content">
                        <div class="login-logo">
                            <a href="#">
                                <img src="<?= asset('storage/' . $system->systemlogo) ?>" style="width: 80px;">
                            </a>
                        </div>
                        <div class="login-form">
                            <?php if (session('error')) { ?>
                                <div class="alert alert-danger"><?= session('error') ?></div>
                            <?php } ?>
                            <?php if (session('success')) { ?>
                                <div class="alert alert-success"><?= session('success') ?></div>
                            <?php } ?>
                            <form action="/login/process" method="post">
                                @csrf
                                <div class="form-group">
                                    <label>Username</label>
                                    <input class="au-input au-input--full" type="text" name="username" placeholder="Username" value="{{ old('username') }}" autocomplete="username" required>
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input class="au-input au-input--full" type="password" name="password" placeholder="Password" autocomplete="current-password" required>
                                </div>
                                <?php if (!empty($captchaRequired)) { ?>
                                    <div class="form-group" id="captcha-online" style="display:none;">
                                        <?php if (!empty($recaptchaSiteKey)) { ?>
                                            <div class="g-recaptcha" data-sitekey="<?= e($recaptchaSiteKey) ?>"></div>
                                            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                                        <?php } ?>
                                    </div>
                                    <div class="form-group" id="captcha-offline" style="display:none;">
                                        <label>Captcha: <?= e($offlineQuestion ?? '') ?></label>
                                        <input class="au-input au-input--full" type="number" name="captcha_answer" placeholder="Jawaban captcha" inputmode="numeric">
                                    </div>
                                    <script>
                                        (function () {
                                            const onlineWrap = document.getElementById('captcha-online');
                                            const offlineWrap = document.getElementById('captcha-offline');
                                            const hasSiteKey = !!(onlineWrap && onlineWrap.querySelector('.g-recaptcha'));

                                            function updateCaptchaMode() {
                                                const isOnline = navigator.onLine;
                                                if (isOnline && hasSiteKey) {
                                                    if (onlineWrap) onlineWrap.style.display = 'block';
                                                    if (offlineWrap) offlineWrap.style.display = 'none';
                                                } else {
                                                    if (onlineWrap) onlineWrap.style.display = 'none';
                                                    if (offlineWrap) offlineWrap.style.display = 'block';
                                                }
                                            }

                                            window.addEventListener('online', updateCaptchaMode);
                                            window.addEventListener('offline', updateCaptchaMode);
                                            updateCaptchaMode();
                                        })();
                                    </script>
                                <?php } ?>
                                <div class="login-checkbox">
                                    
                                    <label>
                                        <a href="#">Forgotten Password?</a>
                                    </label>
                                </div>
                                <button class="au-btn au-btn--block au-btn--green m-b-20" type="submit">sign in</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
