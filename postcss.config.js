module.exports = {
  plugins: {
    'postcss-preset-env': {},
    'postcss-import': {},
    'postcss-preset-env': {
      features: { 
        'nesting-rules': false
      }
    },
    "@tailwindcss/postcss": {},
    autoprefixer: {}
  }
};
