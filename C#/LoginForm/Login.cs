namespace LoginForm
{
    using System;
    using System.Windows.Forms;

    using MetroFramework.Forms;

    using PassiveAuth;

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
        public AuthWorker Worker { get; set; } = new AuthWorker("https://mysite.com/login.php", "https://mysite.com/register.php", "https://mysite.com/redeemtoken.php");
        public AdminAuthWorker AdminWorker { get; set; } = new AdminAuthWorker("https://mysite.com/generator.php");

        private void LoginButton_Click(object sender, EventArgs e)
        {
            try
            {
                // Attempts to login using the provided login credentials
                var loginResult = Worker.Login(LoginUsername.Text, LoginPassword.Text);

                if (loginResult.UserName != null)
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
                var registerResult = Worker.Register(RegisterUsername.Text, RegisterPassword.Text, RegisterPasswordConfirm.Text);

                if (registerResult.ErrorMessage == null)
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
            userinfo.Text = $"Username: {Worker.CurrentResponse?.UserName}\n" + $"ID: {Worker.CurrentResponse?.Id}\n" + $"EXPIRY: {Worker.CurrentResponse?.ExpiryTime}";
        }

        private void AddToken_Click(object sender, EventArgs e)
        {
            try
            {
                // Sends all the token creation data to admin-auth-worker
                var res = AdminWorker.AddToken((int)yearcount.Value, (int)monthcount.Value, (int)weekcount.Value, (int)daycount.Value, (int)licquantity.Value, (int)level.Value, addSecurity.Text);

                if (res.ErrorMessage == null)
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
                var res = Worker.Redeem(Worker.CurrentResponse.UserName, TokenBox.Text);

                if (res.ErrorMessage != null)
                {
                    MessageBox.Show(res.ErrorMessage);
                }
                else
                {
                    MessageBox.Show(res.SuccessMessage);
                }
            }
            catch (Exception exception)
            {
                MessageBox.Show(exception.ToString());
            }
        }
    }
}
