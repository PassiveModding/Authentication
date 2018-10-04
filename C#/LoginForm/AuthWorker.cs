namespace PassiveAuth
{
    using System;
    using System.Net;

    using Newtonsoft.Json;

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
        public AuthWorker(string loginUrl, string registerUrl, string tokenRedemptionUrl)
        {
            LoginUrl = loginUrl;
            RegisterUrl = registerUrl;
            TokenRedemptionUrl = tokenRedemptionUrl;
        }

        private string RegisterUrl { get; }
        private string LoginUrl { get; }
        private string TokenRedemptionUrl { get; }

        /// <summary>
        /// Shows the most recently received response
        /// </summary>
        public Response CurrentResponse { get; set; }

        public class Response
        {
            /// <summary>
            /// The user ID
            /// </summary>
            public int Id { get; set; }

            /// <summary>
            /// The Username
            /// </summary>
            public string UserName { get; set; }

            /// <summary>
            /// The user access level
            /// </summary>
            public int AccessLevel { get; set; }

            /// <summary>
            /// The time at which the user's upgrades will expire
            /// </summary>
            public DateTime ExpiryTime { get; set; }

            /// <summary>
            /// Success message in the case that the task succeeds
            /// </summary>
            public string SuccessMessage { get; set; }

            /// <summary>
            /// Error message if the task fails
            /// </summary>
            public string ErrorMessage { get; set; }
        }

        /// <summary>
        /// Tries to login via login.php
        /// </summary>
        /// <param name="username">Your plaintext username</param>
        /// <param name="password">Your plaintext password</param>
        /// <returns></returns>
        public Response Login(string username, string password)
        {
            var parameters = $"username={username}&password={password}";

            using (WebClient client = new WebClient())
            {
                client.Headers[HttpRequestHeader.ContentType] = "application/x-www-form-urlencoded";
                var result = client.UploadString(LoginUrl, parameters);
                var res = JsonConvert.DeserializeObject<Response>(result);
                CurrentResponse = res;
                return res;
            }
        }

        /// <summary>
        /// Tries to register via register.php
        /// </summary>
        /// <param name="username">Your plaintext username</param>
        /// <param name="password">Your plaintext password</param>
        /// /// <param name="passwordConfirm">Your plaintext password confirmation</param>
        /// <returns></returns>
        public Response Register(string username, string password, string passwordConfirm)
        {
            var parameters = $"username={username}&password={password}&passwordconfirm={passwordConfirm}";

            using (WebClient client = new WebClient())
            {
                client.Headers[HttpRequestHeader.ContentType] = "application/x-www-form-urlencoded";
                var result = client.UploadString(RegisterUrl, parameters);
                var res = JsonConvert.DeserializeObject<Response>(result);
                CurrentResponse = res;
                return res;
            }
        }

        /// <summary>
        /// Attempts to redeem a token for the specified user
        /// </summary>
        /// <param name="username">the username</param>
        /// <param name="token">the token</param>
        /// <returns></returns>
        public Response Redeem(string username, string token)
        {
            var parameters = $"username={username}&token={token}";

            using (WebClient client = new WebClient())
            {
                client.Headers[HttpRequestHeader.ContentType] = "application/x-www-form-urlencoded";
                var result = client.UploadString(TokenRedemptionUrl, parameters);
                var res = JsonConvert.DeserializeObject<Response>(result);
                CurrentResponse = res;
                return res;
            }
        }
    }
}
