namespace LoginForm
{
    using System;
    using System.Windows.Forms;

    using PassiveAuth;

    using MetroFramework.Forms;

    /// <summary>
    /// An example form demonstrating usage of PassiveAuth
    /// </summary>
    public partial class Login : MetroForm
    {
        public Login()
        {
            InitializeComponent();
        }

        private void Form1_Load(object sender, EventArgs e)
        {
        }

        /// <summary>
        /// Initialize the worker and admin-worker classes using your urls that point to your login.register/redeem/generator files
        /// </summary>
        public AuthWorker Worker { get; set; } = new AuthWorker(
            "http://localhost/php_deploy/login.php", 
            "http://localhost/php_deploy/register.php",
            "http://localhost/php_deploy/redeemtoken.php", 
            "http://localhost/php_deploy/Recovery/recover.php",
            "ANY_RANDOM_STRING");
        public AdminAuthWorker AdminWorker { get; set; } = new AdminAuthWorker(
            "http://localhost/php_deploy/Admin/generator.php",
            "ANY_RANDOM_STRING");

        public LoginResponse CurrentLoginResponse { get; set; }

        private void LoginButton_Click(object sender, EventArgs e)
        {
            try
            {
                // Attempts to login using the provided login credentials
                var loginResult = Worker.Login(LoginUsername.Text, LoginPassword.Text);

                CurrentLoginResponse = loginResult;

                if (loginResult.Success)
                {
                    MessageBox.Show($"[{loginResult.Id}] Logged In");
                }
                else
                {
                    MessageBox.Show(loginResult.ErrorMessage);
                }
            }
            catch (Exception exception)
            {
                MessageBox.Show(exception.ToString());
            }
        }

        private void RegisterButton_Click(object sender, EventArgs e)
        {
            try
            {
                // Try to register using the provided username and password
                var registerResult = Worker.Register(RegisterUsername.Text, RegisterPassword.Text, RegisterPasswordConfirm.Text, emailbox.Text);

                if (registerResult.Success)
                {
                    MessageBox.Show(registerResult.SuccessMessage);
                }
                else
                {
                    MessageBox.Show(registerResult.ErrorMessage);
                }
            }
            catch (Exception exception)
            {
                MessageBox.Show(exception.ToString());
            }
        }

        private void timer1_Tick(object sender, EventArgs e)
        {
            // Refreshes the userinfo label with the most current data
            userinfo.Text = $"Username: {CurrentLoginResponse?.UserName}\n" + $"ID: {CurrentLoginResponse?.Id}\n" + $"EXPIRY: {CurrentLoginResponse?.Expiry_Date}";
        }

        private void AddToken_Click(object sender, EventArgs e)
        {
            try
            {
                // Sends all the token creation data to admin-auth-worker
                var res = AdminWorker.AddToken((int)yearcount.Value, (int)monthcount.Value, (int)weekcount.Value, (int)daycount.Value, (int)licquantity.Value, (int)level.Value, addSecurity.Text);

                if (res.Success)
                {
                    MessageBox.Show("The following licenses have been copied to the clipboard:\n" + string.Join("\n", res.TokenList));
                    Clipboard.SetText(string.Join("\n", res.TokenList));
                }
                else
                {
                    MessageBox.Show(res.ErrorMessage);
                }
            }
            catch (Exception exception)
            {
                MessageBox.Show(exception.ToString());
            }
        }

        private void TokenButton_Click_1(object sender, EventArgs e)
        {
            try
            {
                // Try to redeem a token on the provided username
                // NOTE: The username is being pulled from the current-response, you should probably change this in the case that you aren't logged in or want to apply these to a specific account
                var res = Worker.Redeem(LoginUsername.Text, TokenBox.Text);

                if (res.Success)
                {
                    MessageBox.Show(res.SuccessMessage);
                    CurrentLoginResponse.Expiry_Date = res.Expiry_Date;
                }
                else
                {
                    MessageBox.Show(res.ErrorMessage);
                }
            }
            catch (Exception exception)
            {
                MessageBox.Show(exception.ToString());
            }
        }

        private void RecoverSubmit_Click(object sender, EventArgs e)
        {
            try
            {
                Worker.Recover(recoverEmail.Text);
            }
            catch (Exception exception)
            {
                MessageBox.Show(exception.ToString());
            }
        }
    }
}
