namespace PassiveAuth
{
    using System.Collections.Generic;
    using System.Net;

    using Newtonsoft.Json;

    public class AdminAuthWorker
    {
        /// <summary>
        /// Initializes a new instance of the <see cref="AdminAuthWorker"/> class.
        /// </summary>
        /// <param name="generatorUrl">
        /// </param>
        public AdminAuthWorker(string generatorUrl)
        {
            GeneratorUrl = generatorUrl;
        }

        /// <summary>
        /// Indicates the url used for generating tokens
        /// </summary>
        private string GeneratorUrl { get; set; }

        public class TokenGenerationResponse
        {
            /// <summary>
            /// A list of generated tokens (if the task succeeds)
            /// </summary>
            public List<string> TokenList { get; set; }

            /// <summary>
            /// An error message (if the task fails)
            /// </summary>
            public string ErrorMessage { get; set; }
        }


        /// <summary>
        /// Adds the specified amount of tokens to the database
        /// </summary>
        /// <param name="years">The amount of years each token will be given</param>
        /// <param name="months">The amount of months each token will be given</param>
        /// <param name="weeks">The amount of weeks each token will be given</param>
        /// <param name="days">The amount of days each token will be given</param>
        /// <param name="quantity">the amount of tokens to be generated</param>
        /// <param name="level">The user level for the token</param>
        /// <param name="verificationKey">A pre-set key, this can be found in your generator.php file</param>
        /// <returns></returns>
        public TokenGenerationResponse AddToken(int years, int months, int weeks, int days, int quantity, int level, string verificationKey)
        {
            var parameters = $"verification={verificationKey}&years={years}&months={months}&weeks={weeks}&days={days}&quantity={quantity}&userlevel={level}";

            using (WebClient client = new WebClient())
            {
                client.Headers[HttpRequestHeader.ContentType] = "application/x-www-form-urlencoded";
                var result = client.UploadString(GeneratorUrl, parameters);
                var res = JsonConvert.DeserializeObject<TokenGenerationResponse>(result);
                return res;
            }
        }
    }
}
