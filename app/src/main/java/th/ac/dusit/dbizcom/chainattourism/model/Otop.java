package th.ac.dusit.dbizcom.chainattourism.model;

import com.google.gson.annotations.SerializedName;

import java.util.List;

public class Otop {

    @SerializedName("id")
    public final int id;
    @SerializedName("name")
    public final String name;
    @SerializedName("district")
    public final String district;
    @SerializedName("sub_district")
    public final String subDistrict;
    @SerializedName("village")
    public final String village;
    @SerializedName("address")
    public final String address;
    @SerializedName("details")
    public final String details;
    @SerializedName("price")
    public final int price;
    @SerializedName("contact_url")
    public final String contactUrl;
    @SerializedName("phone")
    public final String phone;
    @SerializedName("opening_time")
    public final String openingTime;
    @SerializedName("latitude")
    public final double latitude;
    @SerializedName("longitude")
    public final double longitude;
    @SerializedName("image_list")
    public final String listImage;
    @SerializedName("image_cover")
    public final String coverImage;
    @SerializedName("recommend")
    public final boolean recommend;
    @SerializedName("gallery_images")
    public final List<String> galleryImages;
    @SerializedName("average_rate")
    public float averageRate;

    public Otop(int id, String name, String district, String subDistrict, String village,
                String address, String details, int price, String contactUrl, String phone,
                String openingTime, double latitude, double longitude, String listImage, String coverImage,
                boolean recommend, List<String> galleryImages, float averageRate) {
        this.id = id;
        this.name = name;
        this.district = district;
        this.subDistrict = subDistrict;
        this.village = village;
        this.address = address;
        this.details = details;
        this.price = price;
        this.contactUrl = contactUrl;
        this.phone = phone;
        this.openingTime = openingTime;
        this.latitude = latitude;
        this.longitude = longitude;
        this.listImage = listImage;
        this.coverImage = coverImage;
        this.recommend = recommend;
        this.galleryImages = galleryImages;
        this.averageRate = averageRate;
    }

    public void setAverageRate(float averageRate) {
        this.averageRate = averageRate;
    }
}
