const { resolve } = require('path')
const webpack = require('webpack')
const ExtractTextPlugin = require("extract-text-webpack-plugin")

module.exports = {
    entry: {
        client: [
            'react-hot-loader/patch',
            'webpack-dev-server/client?http://localhost:8080',
            'webpack/hot/only-dev-server',
            './sass/app.sass',
            './js/client.js',
        ],
    },
    output: {
        filename: '[name].bundle.js',
        path: resolve(__dirname, 'public/assets'),
        publicPath: '/assets/',
    },
    resolve: {
        extensions: ['.js', '.jsx'],
    },
    context: resolve(__dirname, 'resources/assets'),
    devtool: 'inline-source-map',
    devServer: {
        hot: true,
        contentBase: false,
        publicPath: '/assets/',
        proxy: {
            "/": "http://localhost:8000"
        }
    },
    module: {
        rules: [
            {
                test: /\.jsx?$/,
                use: ['babel-loader'],
                exclude: /node_modules/,
            },
            {
                test: /\.(sass|scss)$/,
                use: ExtractTextPlugin.extract(['css-loader', 'sass-loader']),
            },
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader?modules', 'postcss-loader'],
            },
            {
                test: /\.(ico|jpg|png|gif|eot|otf|webp|svg|ttf|woff|woff2)(\?.*)?$/,
                loader: 'file-loader?name=[name].[hash].[ext]'
            },
        ],
    },
    plugins: [
        new webpack.HotModuleReplacementPlugin(),
        new webpack.NamedModulesPlugin(),
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery',
            React: 'react',
        }),
        new ExtractTextPlugin("[name].bundle.css"),
    ],
}
