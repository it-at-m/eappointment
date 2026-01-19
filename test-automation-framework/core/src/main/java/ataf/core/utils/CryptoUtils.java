package ataf.core.utils;

import ataf.core.assertions.CustomAssertions;
import org.apache.commons.lang3.ArrayUtils;

import javax.crypto.BadPaddingException;
import javax.crypto.Cipher;
import javax.crypto.IllegalBlockSizeException;
import javax.crypto.NoSuchPaddingException;
import javax.crypto.SecretKey;
import javax.crypto.SecretKeyFactory;
import javax.crypto.spec.GCMParameterSpec;
import javax.crypto.spec.PBEKeySpec;
import javax.crypto.spec.SecretKeySpec;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.nio.ByteBuffer;
import java.nio.charset.StandardCharsets;
import java.nio.file.Path;
import java.security.InvalidAlgorithmParameterException;
import java.security.InvalidKeyException;
import java.security.NoSuchAlgorithmException;
import java.security.SecureRandom;
import java.security.spec.InvalidKeySpecException;
import java.security.spec.KeySpec;
import java.util.Arrays;
import java.util.Base64;

/**
 * This class provides utility methods for performing AES-256 encryption and decryption of text. It
 * includes methods to generate a secure AES key, encrypt
 * plaintext, and decrypt ciphertext. The encryption uses the AES/GCM/NoPadding algorithm, which is
 * a secure mode of operation for authenticated encryption.
 *
 * <p>
 * All encryption operations require a secret key, which must be set before performing any
 * encryption or decryption. The class also handles nonce and salt
 * generation internally.
 * </p>
 *
 * <p>
 * Note: It is crucial to handle secrets carefully and to clear them from memory after use to
 * prevent unauthorized access.
 * </p>
 *
 * @author mkyong.com, Ludwig Haas (ex.haas02)
 */
public class CryptoUtils {
    private static final String ENCRYPT_ALGO = "AES/GCM/NoPadding";
    private static final int TAG_LENGTH_BIT = 128; // must be one of {128, 120, 112, 104, 96}
    private static final int IV_LENGTH_BYTE = 12; // Length of the Initialization Vector
    private static final int SALT_LENGTH_BYTE = 16; // Length of the salt
    private static char[] secret; // Secret key for encryption and decryption
    private static String encryptedFileExtension = ".encrypted";

    /**
     * Generates a randomly generated nonce (number used once) of the specified size.
     *
     * @param numBytes The number of bytes to generate as a nonce. Must be a positive integer (greater
     *            than 0).
     * @return A byte array containing the generated nonce.
     * @throws IllegalArgumentException if the number of bytes is less than or equal to zero.
     */
    private static byte[] getRandomNonce(int numBytes) {
        assert (numBytes > 0) : "Nonce size must be positive!";
        byte[] nonce = new byte[numBytes];
        new SecureRandom().nextBytes(nonce);
        return nonce;
    }

    /**
     * Creates an AES-256 secret key from the provided salt using PBKDF2 with HMAC-SHA256.
     *
     * @param salt The salt used to strengthen the secret key.
     * @return A {@link SecretKey} instance used for encryption and decryption.
     * @throws NoSuchAlgorithmException if the algorithm is not found.
     * @throws InvalidKeySpecException if the key specification is invalid.
     * @throws IllegalArgumentException if no secret has been set.
     */
    private static SecretKey getAESKey(byte[] salt) throws NoSuchAlgorithmException, InvalidKeySpecException {
        SecretKeyFactory factory = SecretKeyFactory.getInstance("PBKDF2WithHmacSHA256");
        // Validate that the secret has been set
        CustomAssertions.assertFalse(ArrayUtils.isEmpty(secret), "Cannot create AES key as no secret was given!");

        // Key generation parameters
        KeySpec spec = new PBEKeySpec(secret, salt, 65536, 256); // 65536 iterations, 256-bit key
        return new SecretKeySpec(factory.generateSecret(spec).getEncoded(), "AES");
    }

    /**
     * Encrypts the given plaintext using AES-256 encryption.
     *
     * @param plainText The plaintext that will be encrypted.
     * @return A base64-encoded string of the AES-encrypted text.
     * @throws InvalidKeySpecException if the key specification is invalid.
     * @throws NoSuchAlgorithmException if the algorithm is not found.
     * @throws NoSuchPaddingException if the padding scheme is not available.
     * @throws InvalidAlgorithmParameterException if the algorithm parameters are invalid.
     * @throws InvalidKeyException if the key is invalid.
     * @throws BadPaddingException if padding is invalid.
     * @throws IllegalBlockSizeException if the block size is invalid.
     */
    public static String encrypt(String plainText)
            throws InvalidKeySpecException, NoSuchAlgorithmException, NoSuchPaddingException,
            InvalidAlgorithmParameterException, InvalidKeyException, BadPaddingException, IllegalBlockSizeException {

        // Generate salt and IV
        byte[] salt = CryptoUtils.getRandomNonce(SALT_LENGTH_BYTE);
        byte[] iv = CryptoUtils.getRandomNonce(IV_LENGTH_BYTE);

        // Create secret key from password
        SecretKey aesKeyFromPassword = CryptoUtils.getAESKey(salt);

        Cipher cipher = Cipher.getInstance(ENCRYPT_ALGO);

        // Initialize the cipher in encryption mode with the generated IV
        cipher.init(Cipher.ENCRYPT_MODE, aesKeyFromPassword, new GCMParameterSpec(TAG_LENGTH_BIT, iv));

        // Perform encryption
        byte[] cipherText = cipher.doFinal(plainText.getBytes(StandardCharsets.UTF_8));

        // Prefix IV and Salt to the cipher text for later decryption
        byte[] cipherTextWithIvSalt = ByteBuffer.allocate(iv.length + salt.length + cipherText.length)
                .put(iv)
                .put(salt)
                .put(cipherText)
                .array();

        // Return the base64-encoded string representation of the encrypted data
        return Base64.getEncoder().encodeToString(cipherTextWithIvSalt);
    }

    /**
     * Decrypts the given encrypted text using AES-256 decryption.
     *
     * @param encryptedText The encrypted text to decrypt.
     * @return The decrypted plaintext.
     * @throws InvalidKeySpecException if the key specification is invalid.
     * @throws NoSuchAlgorithmException if the algorithm is not found.
     * @throws NoSuchPaddingException if the padding scheme is not available.
     * @throws InvalidAlgorithmParameterException if the algorithm parameters are invalid.
     * @throws InvalidKeyException if the key is invalid.
     * @throws BadPaddingException if padding is invalid.
     * @throws IllegalBlockSizeException if the block size is invalid.
     */
    public static String decrypt(String encryptedText)
            throws InvalidKeySpecException, NoSuchAlgorithmException, NoSuchPaddingException,
            InvalidAlgorithmParameterException, InvalidKeyException, BadPaddingException, IllegalBlockSizeException {

        // Decode the base64-encoded string
        byte[] decode = Base64.getDecoder().decode(encryptedText.getBytes(StandardCharsets.UTF_8));

        // Extract the IV and salt from the decoded data
        ByteBuffer bb = ByteBuffer.wrap(decode);
        byte[] iv = new byte[IV_LENGTH_BYTE];
        bb.get(iv);

        byte[] salt = new byte[SALT_LENGTH_BYTE];
        bb.get(salt);

        byte[] cipherText = new byte[bb.remaining()];
        bb.get(cipherText);

        // Create secret key using the extracted salt
        SecretKey aesKeyFromPassword = CryptoUtils.getAESKey(salt);

        Cipher cipher = Cipher.getInstance(ENCRYPT_ALGO);

        // Initialize the cipher in decryption mode with the extracted IV
        cipher.init(Cipher.DECRYPT_MODE, aesKeyFromPassword, new GCMParameterSpec(TAG_LENGTH_BIT, iv));

        // Perform decryption
        byte[] plainText = cipher.doFinal(cipherText);

        return new String(plainText, StandardCharsets.UTF_8);
    }

    /**
     * Encrypts a file using AES-256 encryption and appends the `.encrypted` extension.
     *
     * @param inputFile The file to encrypt.
     * @return The path to the encrypted file with the `.encrypted` extension.
     * @throws IOException if an I/O error occurs.
     * @throws InvalidKeySpecException if the key specification is invalid.
     * @throws NoSuchAlgorithmException if the algorithm is not found.
     * @throws NoSuchPaddingException if the padding scheme is not available.
     * @throws InvalidAlgorithmParameterException if the algorithm parameters are invalid.
     * @throws InvalidKeyException if the key is invalid.
     * @throws BadPaddingException if padding is invalid.
     * @throws IllegalBlockSizeException if the block size is invalid.
     */
    public static Path encryptFile(Path inputFile)
            throws IOException, InvalidKeySpecException, NoSuchAlgorithmException,
            NoSuchPaddingException, InvalidAlgorithmParameterException, InvalidKeyException,
            BadPaddingException, IllegalBlockSizeException {

        Path outputFile = inputFile.resolveSibling(inputFile.getFileName().toString() + encryptedFileExtension);

        // Generate salt and IV
        byte[] salt = getRandomNonce(SALT_LENGTH_BYTE);
        byte[] iv = getRandomNonce(IV_LENGTH_BYTE);

        // Create secret key
        SecretKey aesKey = getAESKey(salt);

        Cipher cipher = Cipher.getInstance(ENCRYPT_ALGO);
        cipher.init(Cipher.ENCRYPT_MODE, aesKey, new GCMParameterSpec(TAG_LENGTH_BIT, iv));

        try (FileInputStream fis = new FileInputStream(inputFile.toFile());
                FileOutputStream fos = new FileOutputStream(outputFile.toFile())) {

            // Write IV and salt to the beginning of the output file
            fos.write(iv);
            fos.write(salt);

            // Encrypt and write file data in chunks
            byte[] buffer = new byte[1024];
            int bytesRead;
            while ((bytesRead = fis.read(buffer)) != -1) {
                byte[] encryptedChunk = cipher.update(buffer, 0, bytesRead);
                fos.write(encryptedChunk);
            }
            // Finalize encryption
            byte[] finalChunk = cipher.doFinal();
            fos.write(finalChunk);
        }

        return outputFile.toAbsolutePath();
    }

    /**
     * Decrypts a file encrypted using AES-256 and removes the `.encrypted` extension.
     *
     * @param encryptedFile The encrypted file.
     * @return The path to the decrypted file with the original file name (extension removed).
     * @throws IOException if an I/O error occurs.
     * @throws InvalidKeySpecException if the key specification is invalid.
     * @throws NoSuchAlgorithmException if the algorithm is not found.
     * @throws NoSuchPaddingException if the padding scheme is not available.
     * @throws InvalidAlgorithmParameterException if the algorithm parameters are invalid.
     * @throws InvalidKeyException if the key is invalid.
     * @throws BadPaddingException if padding is invalid.
     * @throws IllegalBlockSizeException if the block size is invalid.
     */
    public static Path decryptFile(Path encryptedFile)
            throws IOException, InvalidKeySpecException, NoSuchAlgorithmException,
            NoSuchPaddingException, InvalidAlgorithmParameterException, InvalidKeyException,
            BadPaddingException, IllegalBlockSizeException {

        if (!encryptedFile.getFileName().toString().endsWith(encryptedFileExtension)) {
            throw new IllegalArgumentException("File does not have the expected extension: " + encryptedFileExtension);
        }

        String originalFileName = encryptedFile.getFileName().toString()
                .replace(encryptedFileExtension, "");
        Path outputFile = encryptedFile.resolveSibling(originalFileName);

        try (FileInputStream fis = new FileInputStream(encryptedFile.toFile());
                FileOutputStream fos = new FileOutputStream(outputFile.toFile())) {

            // Read IV and salt from the input file
            byte[] iv = new byte[IV_LENGTH_BYTE];
            if (fis.read(iv) != iv.length) {
                throw new IOException("Invalid encrypted file: IV missing or incomplete.");
            }

            byte[] salt = new byte[SALT_LENGTH_BYTE];
            if (fis.read(salt) != salt.length) {
                throw new IOException("Invalid encrypted file: Salt missing or incomplete.");
            }

            // Create secret key
            SecretKey aesKey = getAESKey(salt);

            Cipher cipher = Cipher.getInstance(ENCRYPT_ALGO);
            cipher.init(Cipher.DECRYPT_MODE, aesKey, new GCMParameterSpec(TAG_LENGTH_BIT, iv));

            // Decrypt and write file data in chunks
            byte[] buffer = new byte[1024];
            int bytesRead;
            while ((bytesRead = fis.read(buffer)) != -1) {
                byte[] decryptedChunk = cipher.update(buffer, 0, bytesRead);
                fos.write(decryptedChunk);
            }
            // Finalize decryption
            byte[] finalChunk = cipher.doFinal();
            fos.write(finalChunk);
        }

        return outputFile.toAbsolutePath();
    }

    /**
     * Sets a custom file extension for encryption and decryption.
     *
     * @param encryptedFileExtension The file extension used for encryption and decryption.
     */
    public static void setCustomFileExtension(String encryptedFileExtension) {
        CryptoUtils.encryptedFileExtension = encryptedFileExtension;
    }

    /**
     * Sets a custom secret for encryption and decryption.
     *
     * @param secret The secret (password) used for encryption and decryption.
     */
    public static void setSecret(char[] secret) {
        CryptoUtils.secret = secret;
    }

    /**
     * Clears the secret from memory for security purposes.
     *
     * <p>
     * This method replaces the contents of the secret array with null characters and then nullifies the
     * reference to ensure that sensitive information is not
     * left in memory.
     * </p>
     */
    public static void clearSecret() {
        if (ArrayUtils.isNotEmpty(secret)) {
            Arrays.fill(secret, '\0');
            secret = new char[0]; // Clear reference
            secret = null; // Nullify reference
        }
    }
}
