(function(){

  //Initialize Firebase
  var config = {
    apiKey: "AIzaSyDQJanFtvFwxSvsHkHWOzDclSu6ME-mMyI",
    authDomain: "db-project-94b67.firebaseapp.com",
    databaseURL: "https://db-project-94b67.firebaseio.com",
    projectId: "db-project-94b67",
    storageBucket: "db-project-94b67.appspot.com",
    messagingSenderId: "769695471668"
  };
  firebase.initializeApp(config);
  
  //Get elements
  const txtEmail = document.getElementById('txtEmail');
  const txtPassword = document.getElementById('txtPass');
  const LoginBtn = document.getElementById('loginBtn');
  const SignupBtn = document.getElementById('SignUpBtn');
  const LogoutBtn = document.getElementById('LogoutBtn');

  //Add login event
  loginBtn.addEventListener('click', e=> {
    //Get email and password
    const email = txtEmail.value;
    const pass = txtPassword.value;
    const auth = firebase.auth();
    //Sign In
    const promise = auth.signInWithEmailAndPassword(email,pass);
    promise.catch(e => console.log(e.message));
  });
  
  //Add sign in event
  SignUpBtn.addEventListener('click', e=> {
    //Get email and password
    const email = txtEmail.value;
    const pass = txtPassword.value;
    const auth = firebase.auth();
    //Create User
    const promise = auth.createUserWithEmailAndPassword(email,pass);
    promise.catch(e => console.log(e.message));
    
  });

  LogoutBtn.addEventListener('click', e => {
    firebase.auth().signOut();
  });

  firebase.auth().onAuthStateChanged(firebaseUser => {
    if(firebaseUser){
      console.log(firebaseUser);
      LogoutBtn.classList.remove('hide');
    } else {
      console.log('not logged in');
      LogoutBtn.classList.add('hide');
    }
  });

}());
