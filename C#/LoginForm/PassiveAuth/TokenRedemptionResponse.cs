namespace PassiveAuth
{
    using System;

    public class TokenRedemptionResponse
    {
        public string UserName { get; set; }

        public string Token_Redeemed { get; set; }

        public DateTime Expiry_Date { get; set; }

        public int Years { get; set; }

        public int Months { get; set; }

        public int Weeks { get; set; }

        public int Days { get; set; }

        public int AccessLevel { get; set; }

        public string SuccessMessage { get; set; }

        public string ErrorMessage { get; set; }

        public bool Success { get; set; }
    }
}
