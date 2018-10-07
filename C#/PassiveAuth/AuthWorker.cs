namespace PassiveAuth
{
    using System;
    using System.Collections.Generic;

    using PassiveAuth.Methods;

    public class AuthWorker
    {
        /// <summary>
        /// Initializes a new instance of the <see cref="AuthWorker"/> class. 
        /// </summary>
        /// <param name="loginUrl">
        /// The url that points to your login.php file
        /// </param>
        /// <param name="registerUrl">
        /// The url that points to your register.php file
        /// </param>
        /// <param name="tokenRedemptionUrl">
        /// The url that points to your redeem.php file
        /// </param>
        /// <param name="recoverUrl">
        /// The recover Url.
        /// </param>
        /// <param name="key">
        /// The encryption key, MUST Match that set in config.php
        /// </param>
        public AuthWorker(string loginUrl, string registerUrl, string tokenRedemptionUrl, string resetPasswordUrl, string recoverUrl, string key)
        {
            LoginUrl = loginUrl;
            RegisterUrl = registerUrl;
            TokenRedemptionUrl = tokenRedemptionUrl;
            RecoverUrl = recoverUrl;
            ResetPasswordUrl = resetPasswordUrl;
            Safe = new Safe_Transfer(key);
        }

        private Safe_Transfer Safe { get; }
        private string RecoverUrl { get; }
        private string RegisterUrl { get; }
        private string LoginUrl { get; }
        private string TokenRedemptionUrl { get; }
        private string ResetPasswordUrl { get; }

        /// <summary>
        /// Tries to login via login.php
        /// </summary>
        /// <param name="username">Your plaintext username</param>
        /// <param name="password">Your plaintext password</param>
        /// <returns></returns>
        public LoginResponse Login(string username, string password)
        {
            var res = Safe.GetFromFile<LoginResponse>(LoginUrl, new List<Tuple<string, string>>
                                                                    {
                                                                        new Tuple<string, string>("username", username), 
                                                                        new Tuple<string, string>("password", password)
                                                                    });
            return res;
        }

        /// <summary>
        /// Tries to register via register.php
        /// </summary>
        /// <param name="username">
        /// Your plaintext username
        /// </param>
        /// <param name="password">
        /// Your plaintext password
        /// </param>
        /// <param name="passwordConfirm">
        /// Your plaintext password confirmation
        /// </param>
        /// <param name="email">
        /// The email.
        /// </param>
        /// <returns>
        /// </returns>
        public RegistrationResponse Register(string username, string password, string passwordConfirm, string email)
        {
            var res = Safe.GetFromFile<RegistrationResponse>(RegisterUrl, new List<Tuple<string, string>>
                                                                           {
                                                                               new Tuple<string, string>("username", username), 
                                                                               new Tuple<string, string>("password", password),
                                                                               new Tuple<string, string>("passwordconfirm", passwordConfirm),
                                                                               new Tuple<string, string>("email", email),
                                                                           });
            return res;
        }

        public PasswordResetResponse ResetPassword(string username, string old_password, string new_password, string new_password_confirm)
        {
            var res = Safe.GetFromFile<PasswordResetResponse>(ResetPasswordUrl, new List<Tuple<string, string>>
                                                                              {
                                                                                  new Tuple<string, string>("username", username), 
                                                                                  new Tuple<string, string>("current_password", old_password),
                                                                                  new Tuple<string, string>("new_password", new_password),
                                                                                  new Tuple<string, string>("new_password_confirm", new_password_confirm)
                                                                              });
            return res;
        }
        
        /// <summary>
        /// Initializes a password recovery for the specified email address
        /// </summary>
        /// <param name="email"></param>
        public void Recover(string email)
        {
            Safe.SecureRun(RecoverUrl, new List<Tuple<string, string>>
                                                                   {
                                                                       new Tuple<string, string>("email", email),
                                                                   });
        }

        /// <summary>
        /// Attempts to redeem a token for the specified user
        /// </summary>
        /// <param name="username">the username</param>
        /// <param name="token">the token</param>
        /// <returns></returns>
        public TokenRedemptionResponse Redeem(string username, string token)
        {
            var res = Safe.GetFromFile<TokenRedemptionResponse>(TokenRedemptionUrl, new List<Tuple<string, string>>
                                                                              {
                                                                                  new Tuple<string, string>("username", username), 
                                                                                  new Tuple<string, string>("token", token)
                                                                              });
            return res;
        }
    }
}
