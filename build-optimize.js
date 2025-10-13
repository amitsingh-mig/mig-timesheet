#!/usr/bin/env node
/**
 * MIG-TimeSheet Build Optimization Script
 * Combines and minifies CSS/JS files for production
 */

const fs = require('fs');
const path = require('path');

// Configuration
const config = {
    css: {
        input: 'public/css/style.css',
        output: 'public/css/style.css'
    },
    js: {
        input: 'public/js/script.js',
        output: 'public/js/script.js'
    }
};

// Simple minification functions
function minifyCSS(css) {
    return css
        .replace(/\/\*[\s\S]*?\*\//g, '') // Remove comments
        .replace(/\s+/g, ' ') // Replace multiple spaces with single space
        .replace(/;\s*}/g, '}') // Remove semicolon before closing brace
        .replace(/{\s*/g, '{') // Remove space after opening brace
        .replace(/;\s*/g, ';') // Remove space after semicolon
        .replace(/,\s*/g, ',') // Remove space after comma
        .replace(/:\s*/g, ':') // Remove space after colon
        .replace(/;\s*}/g, '}') // Remove semicolon before closing brace
        .trim();
}

function minifyJS(js) {
    return js
        .replace(/\/\*[\s\S]*?\*\//g, '') // Remove block comments
        .replace(/\/\/.*$/gm, '') // Remove line comments
        .replace(/\s+/g, ' ') // Replace multiple spaces with single space
        .replace(/;\s*}/g, '}') // Remove semicolon before closing brace
        .replace(/{\s*/g, '{') // Remove space after opening brace
        .replace(/;\s*/g, ';') // Remove space after semicolon
        .replace(/,\s*/g, ',') // Remove space after comma
        .replace(/:\s*/g, ':') // Remove space after colon
        .trim();
}

// Build function
function build() {
    console.log('🚀 Starting MIG-TimeSheet build optimization...');
    
    try {
        // Check if input files exist
        if (!fs.existsSync(config.css.input)) {
            console.log('⚠️  CSS input file not found, skipping CSS optimization');
        } else {
            console.log('📝 Processing CSS...');
            const css = fs.readFileSync(config.css.input, 'utf8');
            const minifiedCSS = minifyCSS(css);
            fs.writeFileSync(config.css.output, minifiedCSS);
            console.log(`✅ CSS optimized: ${config.css.output}`);
        }
        
        if (!fs.existsSync(config.js.input)) {
            console.log('⚠️  JS input file not found, skipping JS optimization');
        } else {
            console.log('📝 Processing JavaScript...');
            const js = fs.readFileSync(config.js.input, 'utf8');
            const minifiedJS = minifyJS(js);
            fs.writeFileSync(config.js.output, minifiedJS);
            console.log(`✅ JavaScript optimized: ${config.js.output}`);
        }
        
        console.log('🎉 Build optimization completed successfully!');
        console.log('📊 Performance improvements:');
        console.log('   • Minified CSS and JavaScript files');
        console.log('   • Removed comments and unnecessary whitespace');
        console.log('   • Optimized for faster loading');
        console.log('   • Ready for production deployment');
        
    } catch (error) {
        console.error('❌ Build optimization failed:', error.message);
        process.exit(1);
    }
}

// Run build if called directly
if (require.main === module) {
    build();
}

module.exports = { build, minifyCSS, minifyJS };
