using System;
using System.Collections.Generic;
using System.Text;

namespace PassiveAuth
{
    public class PasswordRecoveryResponse
    {
        public string Email { get; set; }

        public string SuccessMessage { get; set; }

        public string ErrorMessage { get; set; }

        public bool Success { get; set; }
    }
}
