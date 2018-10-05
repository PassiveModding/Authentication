namespace PassiveAuth
{
    using System.Collections.Generic;

    public class TokenGenerationResponse
    {
        /// <summary>
        /// A list of generated tokens (if the task succeeds)
        /// </summary>
        public List<string> TokenList { get; set; }

        public string ErrorMessage { get; set; }

        public int Years { get; set; }

        public int Months { get; set; }

        public int Weeks { get; set; }

        public int Days { get; set; }

        public int Level { get; set; }

        public string SuccessMessage { get; set; }

        public bool Success { get; set; }
    }
}
