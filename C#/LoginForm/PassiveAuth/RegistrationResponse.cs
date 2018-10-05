namespace PassiveAuth
{
    using System;

    public class RegistrationResponse
    {
        public bool Success { get; set; }

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
        public DateTime Expiry_Date { get; set; }

        /// <summary>
        /// The time when the user first registered
        /// </summary>
        public DateTime Registration_Date { get; set; }

        /// <summary>
        /// The email the user registered with
        /// </summary>
        public string Email { get; set; }

        /// <summary>
        /// Success message in the case that the task succeeds
        /// </summary>
        public string SuccessMessage { get; set; }

        /// <summary>
        /// Error message if the task fails
        /// </summary>
        public string ErrorMessage { get; set; }
    }
}
