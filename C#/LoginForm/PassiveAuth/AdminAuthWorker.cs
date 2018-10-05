namespace PassiveAuth
{
    using System;
    using System.Collections.Generic;

    public class AdminAuthWorker
    {
        /// <summary>
        /// Initializes a new instance of the <see cref="AdminAuthWorker"/> class.
        /// </summary>
        /// <param name="generatorUrl">
        /// </param>
        /// <param name="key">
        /// The encryption key (same as stored in config.php)
        /// </param>
        public AdminAuthWorker(string generatorUrl, string key)
        {
            GeneratorUrl = generatorUrl;
            Safe = new Safe_Transfer(key);
        }

        /// <summary>
        /// Indicates the url used for generating tokens
        /// </summary>
        private string GeneratorUrl { get; }

        private Safe_Transfer Safe { get; }

        /// <summary>
        /// Adds the specified amount of tokens to the database
        /// </summary>
        /// <param name="years">The amount of years each token will be given</param>
        /// <param name="months">The amount of months each token will be given</param>
        /// <param name="weeks">The amount of weeks each token will be given</param>
        /// <param name="days">The amount of days each token will be given</param>
        /// <param name="quantity">the amount of tokens to be generated</param>
        /// <param name="level">The user level for the token</param>
        /// <param name="verificationKey">A pre-set key, this can be found in your config.php file</param>
        /// <returns></returns>
        public TokenGenerationResponse AddToken(int years, int months, int weeks, int days, int quantity, int level, string verificationKey)
        {
            var res = Safe.GetFromFile<TokenGenerationResponse>(GeneratorUrl, new List<Tuple<string, string>>
                                                                              {
                                                                                  new Tuple<string, string>("years", years.ToString()), 
                                                                                  new Tuple<string, string>("months", months.ToString()),
                                                                                  new Tuple<string, string>("weeks", weeks.ToString()),
                                                                                  new Tuple<string, string>("days", days.ToString()),
                                                                                  new Tuple<string, string>("quantity", quantity.ToString()),
                                                                                  new Tuple<string, string>("userlevel", level.ToString()),
                                                                                  new Tuple<string, string>("verification", verificationKey)
                                                                              });
            return res;
        }
    }
}
