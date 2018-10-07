namespace PassiveAuth
{
    public class PasswordResetResponse
    {
        public string UserName { get; set; }

        public string SuccessMessage { get; set; }

        public string ErrorMessage { get; set; }

        public bool Success { get; set; }
    }
}
