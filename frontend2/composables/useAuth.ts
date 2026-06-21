// composables/useAuth.ts

export const useAuth = () => {
  const user = useState<any>("auth_user", () => null);

  const setUser = (newUser: any) => {
    user.value = newUser;
  };

  const clearUser = () => {
    user.value = null;
  };

  return {
    user,
    setUser,
    clearUser,
  };
};
